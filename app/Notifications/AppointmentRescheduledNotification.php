<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledNotification extends Notification implements ShouldQueue
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
            ->subject('Appointment time updated')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('The time for an appointment in your BHA Help Ladder portal has been updated.')
            ->line('New date and time: '.$this->appointment->localStart()->format('D, j M Y \a\t g:i a').' WAT')
            ->action('View appointment', route('dashboard'))
            ->line('For privacy, service and counselling details are not included in this email.');
    }
}
