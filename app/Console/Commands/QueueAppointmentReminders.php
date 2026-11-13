<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Notifications\AppointmentReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueAppointmentReminders extends Command
{
    protected $signature = 'appointments:queue-reminders';

    protected $description = 'Queue one discreet upcoming reminder per confirmed appointment';

    public function handle(): int
    {
        $now = CarbonImmutable::now('UTC');
        $until = $now->addHours(config('booking.reminder_lead_hours'));
        $queued = 0;

        Appointment::query()
            ->where('status', AppointmentStatus::Confirmed)
            ->where('starts_at_utc', '>', $now)
            ->where('starts_at_utc', '<=', $until)
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $appointmentId) use (&$queued): void {
                $created = DB::transaction(function () use ($appointmentId): bool {
                    $appointment = Appointment::query()
                        ->with(['client', 'counsellorProfile.user'])
                        ->lockForUpdate()
                        ->find($appointmentId);

                    if (! $appointment || $appointment->status !== AppointmentStatus::Confirmed) {
                        return false;
                    }

                    $reminder = AppointmentReminder::query()->firstOrCreate(
                        ['appointment_id' => $appointment->id, 'kind' => 'upcoming'],
                        ['queued_at' => now('UTC')],
                    );

                    if (! $reminder->wasRecentlyCreated) {
                        return false;
                    }

                    $appointment->client->notify(new AppointmentReminderNotification($appointment));
                    $appointment->counsellorProfile->user->notify(new AppointmentReminderNotification($appointment));

                    return true;
                }, 3);

                if ($created) {
                    $queued++;
                }
            });

        $this->info("Queued reminders for {$queued} appointment(s).");

        return self::SUCCESS;
    }
}
