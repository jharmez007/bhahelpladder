<?php

namespace Tests\Feature\Appointments;

use App\Enums\AppointmentStatus;
use App\Enums\SessionMode;
use App\Models\Appointment;
use App\Models\AvailabilityRule;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MySqlConcurrentBookingTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped('This test requires MySQL/InnoDB and is intentionally not treated as an SQLite concurrency test.');
        }

        if (! str_ends_with((string) config('database.connections.mysql.database'), '_test')) {
            $this->fail('The concurrency test may run only against a database whose name ends in _test.');
        }
    }

    public function test_simultaneous_requests_for_one_counsellor_slot_create_only_one_appointment(): void
    {
        $clients = User::factory()->client()->count(2)->create();
        $profile = CounsellorProfile::factory()->create();
        $service = Service::factory()->create(['buffer_before_minutes' => 10, 'buffer_after_minutes' => 10]);
        $profile->services()->attach($service);

        $localStart = CarbonImmutable::now(config('booking.timezone'))->addDays(3)->setTime(12, 0);
        AvailabilityRule::factory()->create([
            'counsellor_profile_id' => $profile->id,
            'weekday' => $localStart->dayOfWeek,
            'starts_at' => '09:00:00',
            'ends_at' => '17:00:00',
        ]);

        $barrier = microtime(true) + 1.5;
        $processes = $clients->map(fn (User $client) => new Process([
            PHP_BINARY,
            base_path('tests/Fixtures/ConcurrentBookingWorker.php'),
            (string) $client->id,
            (string) $service->id,
            (string) $profile->id,
            $localStart->utc()->toIso8601String(),
            (string) $barrier,
        ], base_path(), [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => (string) config('database.connections.mysql.database'),
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
        ]));

        $processes->each->start();
        $processes->each->wait();

        foreach ($processes as $process) {
            $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        }

        $outcomes = $processes->map(fn (Process $process) => trim($process->getOutput()))->sort()->values()->all();
        $this->assertSame(['CONFLICT', 'CREATED'], $outcomes);
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_simultaneous_reschedules_to_one_slot_move_only_one_appointment_and_preserve_the_loser(): void
    {
        $clients = User::factory()->client()->count(2)->create();
        $profile = CounsellorProfile::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60, 'buffer_before_minutes' => 10, 'buffer_after_minutes' => 10]);
        $profile->services()->attach($service);

        $day = CarbonImmutable::now(config('booking.timezone'))->addDays(3)->startOfDay();
        AvailabilityRule::factory()->create([
            'counsellor_profile_id' => $profile->id,
            'weekday' => $day->dayOfWeek,
            'starts_at' => '09:00:00',
            'ends_at' => '18:00:00',
        ]);

        $sourceStarts = [$day->setTime(10, 0), $day->setTime(14, 0)];
        $appointments = $clients->values()->map(function (User $client, int $index) use ($profile, $service, $sourceStarts): Appointment {
            $start = $sourceStarts[$index]->utc();

            return Appointment::factory()->create([
                'client_id' => $client->id,
                'counsellor_profile_id' => $profile->id,
                'service_id' => $service->id,
                'status' => AppointmentStatus::Confirmed,
                'session_mode' => SessionMode::InPerson,
                'starts_at_utc' => $start,
                'ends_at_utc' => $start->addHour(),
                'buffer_starts_at_utc' => $start->subMinutes(10),
                'buffer_ends_at_utc' => $start->addHour()->addMinutes(10),
            ]);
        });

        $target = $day->setTime(12, 0)->utc();
        $barrier = microtime(true) + 1.5;
        $processes = $appointments->values()->map(fn (Appointment $appointment, int $index) => new Process([
            PHP_BINARY,
            base_path('tests/Fixtures/ConcurrentRescheduleWorker.php'),
            (string) $clients[$index]->id,
            (string) $appointment->id,
            $target->toIso8601String(),
            (string) $barrier,
        ], base_path(), [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => (string) config('database.connections.mysql.database'),
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
        ]));

        $processes->each->start();
        $processes->each->wait();

        foreach ($processes as $process) {
            $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        }

        $outcomes = $processes->map(fn (Process $process) => trim($process->getOutput()))->sort()->values()->all();
        $this->assertSame(['CONFLICT', 'MOVED'], $outcomes);

        $starts = Appointment::query()->orderBy('starts_at_utc')->pluck('starts_at_utc')->map(fn ($start) => CarbonImmutable::parse($start)->setTimezone(config('booking.timezone'))->format('H:i'));
        $this->assertSame(1, $starts->filter(fn (string $start) => $start === '12:00')->count());
        $this->assertTrue($starts->contains('10:00') || $starts->contains('14:00'));
    }
}
