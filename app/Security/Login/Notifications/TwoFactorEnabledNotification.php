<?php

namespace App\Security\Login\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabledNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled')
            ->line('Two-factor authentication has been enabled on your account.')
            ->line('If you did not enable this, please contact support immediately.')
            ->line('Thank you for using VertexCMS.');
    }
}

class LoginNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ip,
        public string $userAgent,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Login Detected')
            ->line("A login to your account was detected from {$this->ip}.")
            ->line("Browser: {$this->userAgent}")
            ->line('If this was not you, please reset your password immediately.')
            ->action('View Login History', url('/admin/security/sessions'));
    }
}

class PasswordExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $daysRemaining,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Expiration Reminder')
            ->line("Your password will expire in {$this->daysRemaining} days.")
            ->line('Please change your password before it expires.')
            ->action('Change Password', url('/admin/password/change'));
    }
}