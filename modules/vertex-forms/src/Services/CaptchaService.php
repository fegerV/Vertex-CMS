<?php

namespace Vertex\Forms\Services;

use Illuminate\Support\Facades\Http;

class CaptchaService
{
    /**
     * Verify Google reCAPTCHA v3 token
     */
    public function verifyRecaptchaV3(string $token, ?string $action = null): array
    {
        $secretKey = config('services.recaptcha.secret_key');
        
        if (!$secretKey) {
            return ['success' => false, 'error' => 'reCAPTCHA secret key not configured'];
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        $data = $response->json();

        if (!($data['success'] ?? false)) {
            return [
                'success' => false,
                'error' => $data['error-codes'][0] ?? 'Verification failed',
            ];
        }

        // Check score threshold (default 0.5)
        $score = $data['score'] ?? 0;
        $threshold = config('services.recaptcha.threshold', 0.5);

        if ($score < $threshold) {
            return [
                'success' => false,
                'error' => 'Score too low',
                'score' => $score,
            ];
        }

        // Check action if provided
        if ($action && ($data['action'] ?? '') !== $action) {
            return [
                'success' => false,
                'error' => 'Action mismatch',
            ];
        }

        return [
            'success' => true,
            'score' => $score,
            'action' => $data['action'] ?? null,
        ];
    }

    /**
     * Verify Cloudflare Turnstile token
     */
    public function verifyTurnstile(string $token): array
    {
        $secretKey = config('services.turnstile.secret_key');
        
        if (!$secretKey) {
            return ['success' => false, 'error' => 'Turnstile secret key not configured'];
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        $data = $response->json();

        if (!($data['success'] ?? false)) {
            return [
                'success' => false,
                'error' => collect($data['error-codes'] ?? [])->join(', '),
            ];
        }

        return [
            'success' => true,
            'challenge_ts' => $data['challenge_ts'] ?? null,
            'hostname' => $data['hostname'] ?? null,
        ];
    }

    /**
     * Verify captcha based on type
     */
    public function verify(string $type, string $token, ?string $action = null): array
    {
        return match(strtolower($type)) {
            'recaptcha_v3' => $this->verifyRecaptchaV3($token, $action),
            'turnstile' => $this->verifyTurnstile($token),
            default => ['success' => false, 'error' => 'Unknown captcha type'],
        };
    }

    /**
     * Get frontend script for captcha
     */
    public static function getScript(string $type): ?string
    {
        return match(strtolower($type)) {
            'recaptcha_v3' => 'https://www.google.com/recaptcha/api.js?render=' . config('services.recaptcha.site_key'),
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
            default => null,
        };
    }

    /**
     * Get site key for frontend
     */
    public static function getSiteKey(string $type): ?string
    {
        return match(strtolower($type)) {
            'recaptcha_v3' => config('services.recaptcha.site_key'),
            'turnstile' => config('services.turnstile.site_key'),
            default => null,
        };
    }
}
