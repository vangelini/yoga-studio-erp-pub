<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reimposta la tua password | Shanti Sadhana')
            ->view('mail.password-reset', [
                'url' => $this->resetUrl($notifiable),
                'expireMinutes' => $this->passwordResetExpireMinutes(),
            ]);
    }

    protected function resetUrl(object $notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    protected function passwordResetExpireMinutes(): int
    {
        $default = config('auth.defaults.passwords');

        return (int) config("auth.passwords.$default.expire", 60);
    }
}
