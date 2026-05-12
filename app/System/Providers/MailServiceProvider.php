<?php

namespace App\System\Providers;

use App\System\Services\EmailService;
use Illuminate\Mail\MailServiceProvider as BaseMailServiceProvider;
use Illuminate\Support\Facades\Config;

class MailServiceProvider extends BaseMailServiceProvider
{
    protected function configureMailer(string $configPath): void
    {
        // Override mail config from settings before parent boot
        $this->app->booted(function () use ($configPath) {
            $this->mergeMailConfigFromSettings();
        });

        parent::configureMailer($configPath);
    }

    private function mergeMailConfigFromSettings(): void
    {
        $driver = config_value('mail.driver');
        if (!$driver) {
            return;
        }

        $config = Config::get('mail');

        $config['default'] = $driver;
        $config['from'] = [
            'address' => config_value('mail.from_address'),
            'name' => config_value('mail.from_name'),
        ];
        $config['reply_to'] = [
            'address' => config_value('mail.reply_to_address'),
            'name' => config_value('mail.reply_to_name'),
        ];

        if ($driver === 'smtp') {
            $config['mailers']['smtp'] = array_merge($config['mailers']['smtp'] ?? [], [
                'transport' => 'smtp',
                'host' => config_value('mail.host'),
                'port' => (int) config_value('mail.port', 587),
                'encryption' => config_value('mail.encryption') ?: null,
                'username' => config_value('mail.username'),
                'password' => config_value('mail.password'),
                'timeout' => null,
                'auth_mode' => null,
            ]);
        }
        // Add other drivers: mailgun, ses, postmark...

        Config::set('mail', $config);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService(
                $app['view'],
                $app['mailer'],
            );
        });
    }
}
