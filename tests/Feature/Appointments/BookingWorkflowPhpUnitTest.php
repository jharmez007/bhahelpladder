<?php

namespace Tests\Feature\Appointments;

use App\Enums\AppointmentStatus;
use App\Enums\SessionMode;
use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Models\AvailabilityBreak;
use App\Models\AvailabilityRule;
use App\Models\BlockedPeriod;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\AppointmentRequestedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Notifications\AppointmentStatusChangedNotification;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingWorkflowPhpUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-09 08:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_role_routes_protect_every_booking_workspace(): void
    {
        $client = User::factory()->client()->create();
        $counsellor = User::factory()->counsellor()->create();
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($client)->get(route('client.appointments.index'))->assertOk();
        $this->actingAs($client)->get(route('client.appointments.book'))->assertOk();
        $this->actingAs($client)->get(route('counsellor.appointments.index'))->assertForbidden();
        $this->actingAs($client)->get(route('admin.appointments.index'))->assertForbidden();
        $this->actingAs($counsellor)->get(route('counsellor.availability'))->assertOk();
        $this->actingAs($counsellor)->get(route('counsellor.appointments.index'))->assertOk();
        $this->actingAs($counsellor)->get(route('client.appointments.index'))->assertForbidden();
        $this->actingAs($administrator)->get(route('admin.booking-setup'))->assertOk();
        $this->actingAs($administrator)->get(route('admin.appointments.index'))->assertOk();
        $this->actingAs($administrator)->get(route('client.appointments.book'))->assertForbidden();
    }

    public function test_availability_honours_breaks_blocks_and_appointment_buffers(): void
    {
        [, $profile, $service] = $this->fixture();
        AvailabilityBreak::query()->create(['counsellor_profile_id' => $profile->id, 'weekday' => 3, 'starts_at' => '12:00:00', 'ends_at' => '13:00:00']);
        BlockedPeriod::query()->create(['counsellor_profile_id' => $profile->id, 'starts_at_utc' => $this->utc('2026-11-11 14:00'), 'ends_at_utc' => $this->utc('2026-11-11 15:00')]);
        Appointment::factory()->create([
            'counsellor_profile_id' => $profile->id, 'service_id' => $service->id,
            'starts_at_utc' => $this->utc('2026-11-11 10:30'), 'ends_at_utc' => $this->utc('2026-11-11 11:30'),
            'buffer_starts_at_utc' => $this->utc('2026-11-11 10:20'), 'buffer_ends_at_utc' => $this->utc('2026-11-11 11:40'),
        ]);

        $labels = collect(app(AvailabilityService::class)->availableSlots($service, $profile, CarbonImmutable::parse('2026-11-11', config('booking.timezone'))))->pluck('label');

        $this->assertContains('3:30 pm', $labels);
        $this->assertNotContains('10:00 am', $labels);
        $this->assertNotContains('11:30 am', $labels);
        $this->assertNotContains('12:00 pm', $labels);
        $this->assertNotContains('2:00 pm', $labels);
    }

    public function test_overlapping_booking_requests_are_rejected(): void
    {
        Notification::fake();
        [$client, $profile, $service] = $this->fixture();
        $start = $this->utc('2026-11-11 10:00');
        app(BookingService::class)->createForClient($client, $service, $profile, SessionMode::InPerson, $start);

        try {
            app(BookingService::class)->createForClient(User::factory()->client()->create(), $service, $profile, SessionMode::InPerson, $start);
            $this->fail('An overlapping request should fail.');
        } catch (BookingConflictException) {
            $this->assertDatabaseCount('appointments', 1);
        }
    }

    public function test_access_and_status_transitions_are_enforced(): void
    {
        Notification::fake();
        [$client, $profile, $service] = $this->fixture();
        $appointment = Appointment::factory()->create(['client_id' => $client->id, 'counsellor_profile_id' => $profile->id, 'service_id' => $service->id]);

        $this->expectException(AuthorizationException::class);
        app(BookingService::class)->transition(User::factory()->counsellor()->create(), $appointment, AppointmentStatus::Confirmed);
    }

    public function test_valid_status_transition_succeeds_and_invalid_transition_fails(): void
    {
        Notification::fake();
        [$client, $profile, $service] = $this->fixture();
        $appointment = Appointment::factory()->create(['client_id' => $client->id, 'counsellor_profile_id' => $profile->id, 'service_id' => $service->id]);
        $confirmed = app(BookingService::class)->transition($profile->user, $appointment, AppointmentStatus::Confirmed);
        $this->assertSame(AppointmentStatus::Confirmed, $confirmed->status);

        $this->expectException(BookingConflictException::class);
        app(BookingService::class)->transition($profile->user, $confirmed, AppointmentStatus::Pending);
    }

    public function test_cancellation_deadline_applies_to_clients_but_not_administrators(): void
    {
        Notification::fake();
        [$client, $profile, $service] = $this->fixture();
        $appointment = Appointment::factory()->create([
            'client_id' => $client->id, 'counsellor_profile_id' => $profile->id, 'service_id' => $service->id,
            'starts_at_utc' => CarbonImmutable::now('UTC')->addHours(12), 'ends_at_utc' => CarbonImmutable::now('UTC')->addHours(13),
            'buffer_starts_at_utc' => CarbonImmutable::now('UTC')->addHours(12)->subMinutes(10), 'buffer_ends_at_utc' => CarbonImmutable::now('UTC')->addHours(13)->addMinutes(10),
        ]);

        try {
            app(BookingService::class)->cancel($client, $appointment);
            $this->fail('The client cancellation deadline should apply.');
        } catch (BookingConflictException) {
            $cancelled = app(BookingService::class)->cancel(User::factory()->administrator()->create(), $appointment);
            $this->assertSame(AppointmentStatus::Cancelled, $cancelled->status);
        }
    }

    public function test_failed_reschedule_preserves_original_then_valid_reschedule_succeeds(): void
    {
        Notification::fake();
        [$client, $profile, $service] = $this->fixture();
        $appointment = app(BookingService::class)->createForClient($client, $service, $profile, SessionMode::Online, $this->utc('2026-11-11 10:00'));
        $original = $appointment->starts_at_utc;

        try {
            app(BookingService::class)->reschedule($client, $appointment, $this->utc('2026-11-11 20:00'));
            $this->fail('An unavailable reschedule should fail.');
        } catch (BookingConflictException) {
            $this->assertTrue($appointment->fresh()->starts_at_utc->equalTo($original));
        }

        $rescheduled = app(BookingService::class)->reschedule($client, $appointment, $this->utc('2026-11-11 15:30'));
        $this->assertSame('15:30', $rescheduled->localStart()->format('H:i'));
    }

    public function test_reminders_are_deduplicated_and_notifications_are_private(): void
    {
        Notification::fake();
        [$client, $profile, $service] = $this->fixture();
        $appointment = Appointment::factory()->create([
            'client_id' => $client->id, 'counsellor_profile_id' => $profile->id, 'service_id' => $service->id,
            'status' => AppointmentStatus::Confirmed, 'session_mode' => SessionMode::Online,
            'starts_at_utc' => CarbonImmutable::now('UTC')->addHours(20), 'ends_at_utc' => CarbonImmutable::now('UTC')->addHours(21),
            'buffer_starts_at_utc' => CarbonImmutable::now('UTC')->addHours(20)->subMinutes(10), 'buffer_ends_at_utc' => CarbonImmutable::now('UTC')->addHours(21)->addMinutes(10),
            'online_meeting_details' => 'https://secret.example/room',
        ]);

        $this->artisan('appointments:queue-reminders')->assertSuccessful();
        $this->artisan('appointments:queue-reminders')->assertSuccessful();
        $this->assertSame(1, $appointment->reminders()->count());
        Notification::assertSentToTimes($client, AppointmentReminderNotification::class, 1);
        Notification::assertSentToTimes($profile->user, AppointmentReminderNotification::class, 1);

        foreach ([
            new AppointmentReminderNotification($appointment->fresh()),
            new AppointmentRequestedNotification($appointment->fresh()),
            new AppointmentRescheduledNotification($appointment->fresh()),
            new AppointmentStatusChangedNotification($appointment->fresh()),
        ] as $notification) {
            $mail = $notification->toMail($client);
            $content = implode(' ', [...$mail->introLines, ...$mail->outroLines]);
            $this->assertStringNotContainsString($service->name, $content);
            $this->assertStringNotContainsString('secret.example', $content);
        }
    }

    /** @return array{User, CounsellorProfile, Service} */
    private function fixture(): array
    {
        $client = User::factory()->client()->create();
        $profile = CounsellorProfile::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60, 'buffer_before_minutes' => 10, 'buffer_after_minutes' => 10]);
        $profile->services()->attach($service);
        AvailabilityRule::factory()->create(['counsellor_profile_id' => $profile->id, 'weekday' => 3, 'starts_at' => '09:00:00', 'ends_at' => '17:00:00']);

        return [$client, $profile, $service];
    }

    private function utc(string $local): CarbonImmutable
    {
        return CarbonImmutable::parse($local, config('booking.timezone'))->utc();
    }
}
