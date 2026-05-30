<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Resetowanie hasła — PiktoFlow')
            ->greeting('Cześć!')
            ->line('Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta w PiktoFlow.')
            ->action('Zresetuj hasło', $url)
            ->line('Jeśli to nie Ty wysłałeś tej prośby, możesz zignorować tę wiadomość. żadne zmiany nie zostaną wprowadzone.')
            ->salutation(' ');
    }
}
