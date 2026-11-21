<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            absolute: false
        );

        return (new MailMessage)
            ->subject('Verify Your LMS Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', url($verificationUrl))
            ->line('This verification link will expire in 24 hours.');
    }
}