<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaValidator
{
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (!$this->enabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        if (!($data['success'] ?? false)) {
            return false;
        }

        if (array_key_exists('score', $data)) {
            $minScore = (float) config('services.recaptcha.min_score', 0.5);
            return (float) $data['score'] >= $minScore;
        }

        return true;
    }

    public function enabled(): bool
    {
        if (!config('services.recaptcha.enabled')) {
            return false;
        }

        return filled(config('services.recaptcha.secret'));
    }
}
