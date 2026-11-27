<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountLockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cuenta fue bloqueada')
            ->line('Tu cuenta fue bloqueada después de '.config('security.lockout.max_failed_attempts').' intentos de inicio de sesión fallidos consecutivos.')
            ->line('Si no fuiste tú, alguien podría estar intentando acceder a tu cuenta.')
            ->line('Contacta a un administrador para desbloquearla.');
    }
}
