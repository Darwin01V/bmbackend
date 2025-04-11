<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanCercaDeVencimientoNotification extends Notification
{
    use Queueable;
    public $plan;
    /**
     * Create a new notification instance.
     */
    public function __construct($plan)
    {
        $this->plan = $plan;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu plan está por vencer')
            ->greeting("Hola {$notifiable->name}")
            ->line("Tu plan actual vence el {$this->plan->date_end}.")
            ->line('Te recomendamos renovarlo para no perder el acceso.')
            ->action('Renovar plan', url('/mi-cuenta/planes')) // Cambia según tu frontend
            ->line('Gracias por confiar en nosotros.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
