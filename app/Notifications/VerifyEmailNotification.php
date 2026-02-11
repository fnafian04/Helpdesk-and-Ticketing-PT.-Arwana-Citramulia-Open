<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
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
        $verificationUrl = $this->verificationUrl($notifiable);
        $config = Config::get('emailverification');
        $appName = Config::get('app.name', 'Helpdesk Ticketing System');
        $expirationMinutes = $config['expiration'];

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email Anda - ' . $appName)
            ->view('emails.verify-email', [
                'appName' => $appName,
                'userName' => $notifiable->name,
                'buttonUrl' => $verificationUrl,
                'expirationMinutes' => $expirationMinutes,
            ]);
    }
    
    /**
     * Generate the verification URL yang redirect ke frontend SPA.
     * URL menggunakan hash dari user id untuk keamanan.
     */
    protected function verificationUrl(object $notifiable): string
    {
        $config = Config::get('emailverification');
        $expirationMinutes = $config['expiration'];

        // Generate signed URL backend untuk verifikasi
        $backendUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expirationMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Buat URL frontend dengan menambahkan backend verification URL sebagai query param
        $frontendUrl = rtrim($config['frontend_url'], '/') . $config['frontend_path'];
        
        return $frontendUrl . '?verify_url=' . urlencode($backendUrl);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
