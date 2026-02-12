<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $otpCode;
    private int $expirationMinutes;

    public function __construct(string $otpCode, int $expirationMinutes = 15)
    {
        $this->otpCode = $otpCode;
        $this->expirationMinutes = $expirationMinutes;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Helpdesk Ticketing System');

        return (new MailMessage)
            ->subject('Kode Reset Password - ' . $appName)
            ->view('emails.password-reset-otp', [
                'appName' => $appName,
                'userName' => $notifiable->name,
                'otpCode' => $this->otpCode,
                'expirationMinutes' => $this->expirationMinutes,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
