<?php

namespace App\Services;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentNotificationDelivery
{
    public function send(object $recipient, Notification $notification): bool
    {
        try {
            $recipient->notify($notification);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Appointment email delivery failed after the booking change was saved.', [
                'notification' => $notification::class,
                'failure_type' => $exception::class,
            ]);

            return false;
        }
    }
}
