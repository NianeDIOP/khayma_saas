<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $provider = (string) config('services.sms.provider', 'log');

        if ($provider === 'log') {
            Log::info('[SMS:LOG]', ['to' => $to, 'message' => $message]);
            return true;
        }

        if ($provider === 'fake') {
            return true;
        }

        if ($provider === 'api') {
            $url   = (string) config('services.sms.url');
            $token = (string) config('services.sms.token');
            $from  = (string) config('services.sms.from', 'KHAYMA');

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
}
