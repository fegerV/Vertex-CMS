<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rate limiters регистрируются в boot, когда все сервисы уже доступны
    }

    public function boot(): void
    {
        $this->registerRateLimiters();
        $this->registerValidationRules();
    }

    private function registerRateLimiters(): void
    {
        // Используем facade только после того как все сервисы зарегистрированы
        \Illuminate\Support\Facades\RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute((int) config('vertex.api.rate_limit.public', 60))
                ->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('api-authenticated', function (Request $request) {
            return Limit::perMinute((int) config('vertex.api.rate_limit.authenticated', 120))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });

        \Illuminate\Support\Facades\RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by((string) $request->input('email', $request->ip()));
        });
    }

    private function registerValidationRules(): void
    {
        // reCAPTCHA validation rule
        Validator::extend('captcha', function ($attribute, $value, $parameters, $validator) {
            $recaptchaResponse = request()->input('g-recaptcha-response');
            
            if (empty($recaptchaResponse)) {
                return false;
            }

            $secretKey = config('services.recaptcha.secret_key');
            if (empty($secretKey)) {
                // Если секретный ключ не настроен, пропускаем проверку (для разработки)
                return true;
            }

            $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => request()->ip(),
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($verifyUrl, false, $context);
            
            if ($result === false) {
                return false;
            }

            $response = json_decode($result, true);
            
            return isset($response['success']) && $response['success'] === true;
        }, 'The captcha verification failed.');
    }
}

