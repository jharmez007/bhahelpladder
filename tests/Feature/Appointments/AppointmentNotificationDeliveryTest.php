<?php

namespace Tests\Feature\Appointments;

use App\Services\AppointmentNotificationDelivery;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class AppointmentNotificationDeliveryTest extends TestCase
{
    public function test_delivery_failure_is_reported_safely_without_being_rethrown(): void
    {
        Log::spy();

        $recipient = new class
        {
            public function notify(Notification $notification): void
            {
                throw new RuntimeException('private-provider-response');
            }
        };

        $notification = new class extends Notification {};

        $this->assertFalse(app(AppointmentNotificationDelivery::class)->send($recipient, $notification));

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Appointment email delivery failed after the booking change was saved.'
                    && ($context['failure_type'] ?? null) === RuntimeException::class
                    && ! str_contains(json_encode($context), 'private-provider-response');
            });
    }
}
