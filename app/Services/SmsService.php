<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $settings = $this->resolveSettings();
        $provider = $settings['provider'];

        if ($provider === 'log') {
            Log::info('[SMS:LOG]', ['to' => $to, 'message' => $message]);
            return true;
        }

        if ($provider === 'fake') {
            return true;
        }

        if ($provider === 'api') {
            $url = $settings['url'];
            $token = $settings['token'];
            $from = $settings['from'];

            if ($url === '' || $token === '') {
                Log::warning('[SMS] Missing api URL or token');
                return false;
            }

            try {
                $res = Http::timeout(8)
                    ->withToken($token)
                    ->post($url, [
                        'to'      => $to,
                        'from'    => $from,
                        'message' => $message,
                    ]);

                return $res->successful();
            } catch (\Throwable $e) {
                Log::error('[SMS] API error', ['error' => $e->getMessage()]);
                return false;
            }
        }

        Log::warning('[SMS] Unknown provider', ['provider' => $provider]);
        return false;
    }

    /**
     * @return array{provider:string,url:string,token:string,from:string}
     */
    private function resolveSettings(): array
    {
        $defaults = [
            'provider' => (string) config('services.sms.provider', 'log'),
            'url' => (string) config('services.sms.url', ''),
            'token' => (string) config('services.sms.token', ''),
            'from' => (string) config('services.sms.from', 'KHAYMA'),
        ];

        try {
            $stored = PlatformSetting::getMany([
                'sms_provider',
                'sms_api_url',
                'sms_api_token',
                'sms_from',
            ]);
        } catch (\Throwable) {
            return $defaults;
        }

        return [
            'provider' => $this->firstFilled($stored['sms_provider'] ?? null, $defaults['provider']),
            'url' => $this->firstFilled($stored['sms_api_url'] ?? null, $defaults['url']),
            'token' => $this->firstFilled($stored['sms_api_token'] ?? null, $defaults['token']),
            'from' => $this->firstFilled($stored['sms_from'] ?? null, $defaults['from']),
        ];
    }

    private function firstFilled(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }
}
