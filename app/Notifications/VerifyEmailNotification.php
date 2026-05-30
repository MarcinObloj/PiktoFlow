<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Potwierdź adres email — PiktoFlow')
            ->greeting('Cześć!')
            ->line('Dziękujemy za rejestracje w PiktoFlow! Kliknij przycisk poniżej, aby potwierdzić swój adres email i aktywować konto.')
            ->action('Potwierdź adres email', $url)
            ->line('Jeśli nie zakładałeś konta w PiktoFlow, możesz zignorować tę wiadomość.')
            ->salutation(' ');
    }
}
