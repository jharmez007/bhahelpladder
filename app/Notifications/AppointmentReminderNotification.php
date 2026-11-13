<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Appointment reminder')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is a discreet reminder about an upcoming appointment in your BHA Help Ladder portal.')
            ->line('Date and time: '.$this->appointment->localStart()->format('D, j M Y \a\t g:i a').' WAT')
            ->action('View appointment', route('dashboard'))
            ->line('Meeting links and counselling details are intentionally omitted from email.');
    }
}
