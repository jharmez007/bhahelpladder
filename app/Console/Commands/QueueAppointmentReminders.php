<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Notifications\AppointmentReminderNotification;
use App\Services\AppointmentNotificationDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueAppointmentReminders extends Command
{
    protected $signature = 'appointments:queue-reminders';

    protected $description = 'Queue one discreet upcoming reminder per confirmed appointment';

    public function __construct(private AppointmentNotificationDelivery $notifications)
    {
        parent::__construct();
    }

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
                $appointment = DB::transaction(function () use ($appointmentId): ?Appointment {
                    $appointment = Appointment::query()
                        ->with(['client', 'counsellorProfile.user'])
                        ->lockForUpdate()
                        ->find($appointmentId);

                    if (! $appointment || $appointment->status !== AppointmentStatus::Confirmed) {
                        return null;
                    }

                    $reminder = AppointmentReminder::query()->firstOrCreate(
                        ['appointment_id' => $appointment->id, 'kind' => 'upcoming'],
                        ['queued_at' => now('UTC')],
                    );

                    if (! $reminder->wasRecentlyCreated) {
                        return null;
                    }

                    return $appointment;
                }, 3);

                if ($appointment) {
                    $this->notifications->send($appointment->client, new AppointmentReminderNotification($appointment));
                    $this->notifications->send($appointment->counsellorProfile->user, new AppointmentReminderNotification($appointment));
                    $queued++;
                }
            });

        $this->info("Queued reminders for {$queued} appointment(s).");

        return self::SUCCESS;
    }
}
