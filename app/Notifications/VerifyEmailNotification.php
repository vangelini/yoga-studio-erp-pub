<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Conferma il tuo indirizzo email | Shanti Sadhana')
            ->view('mail.verify-email', [
                'url' => $this->verificationUrl($notifiable),
            ]);
    }
}
