<?php

namespace Vertex\Forms\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Vertex\Forms\Models\Form;

class FormSpamProtectionService
{
    public function verify(Form $form, Request $request): void
    {
        $settings = $form->settings ?? [];

        if ($settings['turnstile_enabled'] ?? config('forms.turnstile_enabled', false)) {
            $this->verifyTurnstile($request, $settings);
        }

        if ($settings['recaptcha_enabled'] ?? config('forms.recaptcha_enabled', false)) {
            $this->verifyRecaptcha($request, $settings);
        }
    }

    private function verifyRecaptcha(Request $request, array $settings): void
    {
        $version = $settings['recaptcha_version'] ?? config('forms.recaptcha_version', 'v2');
        $field = $version === 'v3' ? 'recaptcha_token' : 'g-recaptcha-response';
        $token = (string) $request->input($field, '');
        $secret = (string) config('forms.recaptcha_secret_key', '');

        if ($token === '' || $secret === '') {
            $this->fail($field, __('forms.validation_captcha_failed'));
        }

        $result = $this->postVerification(
            config('forms.recaptcha_verify_url', 'https://www.google.com/recaptcha/api/siteverify'),
            ['secret' => $secret, 'response' => $token, 'remoteip' => $request->ip()],
            $field
        );

        if (! ($result['success'] ?? false)) {
            $this->fail($field, __('forms.validation_captcha_failed'));
        }

        if ($result['bypassed'] ?? false) {
            return;
        }

        $expectedHostname = (string) config('forms.captcha_hostname', '');
        if ($expectedHostname !== '' && ! hash_equals($expectedHostname, (string) ($result['hostname'] ?? ''))) {
            $this->fail($field, __('forms.validation_captcha_failed'));
        }

        if ($version === 'v3') {
            $minimumScore = (float) ($settings['recaptcha_min_score'] ?? config('forms.recaptcha_min_score', 0.5));
            $expectedAction = (string) ($settings['recaptcha_action'] ?? 'form_submit');

            if ((float) ($result['score'] ?? 0) < $minimumScore
                || ($expectedAction !== '' && ! hash_equals($expectedAction, (string) ($result['action'] ?? '')))) {
                $this->fail($field, __('forms.validation_captcha_failed'));
            }
        }
    }

    private function verifyTurnstile(Request $request, array $settings): void
    {
        $field = 'cf-turnstile-response';
        $token = (string) $request->input($field, '');
        $secret = (string) config('forms.turnstile_secret_key', '');

        if ($token === '' || $secret === '') {
            $this->fail($field, __('forms.validation_captcha_failed'));
        }

        $result = $this->postVerification(
            config('forms.turnstile_verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
            ['secret' => $secret, 'response' => $token, 'remoteip' => $request->ip()],
            $field
        );

        if (! ($result['success'] ?? false)) {
            $this->fail($field, __('forms.validation_captcha_failed'));
        }

        if ($result['bypassed'] ?? false) {
            return;
        }

        $expectedHostname = (string) config('forms.captcha_hostname', '');
        if ($expectedHostname !== '' && ! hash_equals($expectedHostname, (string) ($result['hostname'] ?? ''))) {
            $this->fail($field, __('forms.validation_captcha_failed'));
        }
    }

    private function postVerification(string $url, array $payload, string $field): array
    {
        try {
            $response = Http::asForm()
                ->timeout((int) config('forms.captcha_timeout', 5))
                ->post($url, $payload);
        } catch (ConnectionException) {
            if (config('forms.captcha_fail_closed', true)) {
                $this->fail($field, __('forms.validation_captcha_unavailable'));
            }

            return ['success' => true, 'bypassed' => true];
        }

        if (! $response->successful()) {
            if (config('forms.captcha_fail_closed', true)) {
                $this->fail($field, __('forms.validation_captcha_unavailable'));
            }

            return ['success' => true, 'bypassed' => true];
        }

        return $response->json() ?: [];
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => [$message]]);
    }
}
