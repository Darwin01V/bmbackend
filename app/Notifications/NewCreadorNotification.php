<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCreadorNotification extends Notification
{
    use Queueable;

    protected $creador;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $creador)
    {
        $this->creador = $creador;
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
    public function toMail($notifiable)
    {
        $perfil = $this->creador->perfilCreador;
        
        return (new MailMessage)
                ->subject('Nuevo Creador Registrado')
                ->greeting('¡Hola Administrador!')
                ->line('Un nuevo creador se ha registrado en la plataforma.')
                ->line('Información del creador:')
                ->line("Nombre: {$perfil->first_name} {$perfil->last_name}")
                ->line("Email: {$this->creador->email}")
                ->line("País: {$perfil->country}")
                ->line("Experiencia: {$perfil->experience}")
                ->line("Portafolio: {$perfil->working}")
                ->line("Mas sobre el creador: {$perfil->details}")
                // Se elimina esta línea:
                // ->action('Ver Creador', url('/admin/creadores/' . $this->creador->id))
                ->line('Gracias por usar nuestra plataforma!');
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
