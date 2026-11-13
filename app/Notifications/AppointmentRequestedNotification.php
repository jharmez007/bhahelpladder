<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment request received')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An appointment request is now pending in your BHA Help Ladder portal.')
            ->line('Date and time: '.$this->appointment->localStart()->format('D, j M Y \a\t g:i a').' WAT')
            ->line('Reference: '.strtoupper(substr($this->appointment->reference, 0, 8)))
            ->action('View appointment', route('dashboard'))
            ->line('For privacy, service and counselling details are not included in this email.');
    }
}
