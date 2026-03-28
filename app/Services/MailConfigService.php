<?php

namespace App\Services;

use App\Models\PlatformSetting;

class MailConfigService
{
    private const KEYS = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
    ];

    public function apply(): void
    {
        try {
            $settings = PlatformSetting::getMany(self::KEYS);
        } catch (\Throwable) {
            return;
        }

        $defaultMailer = $this->firstFilled($settings['mail_mailer'] ?? null, (string) config('mail.default', 'log'));
        $smtpHost = $this->firstFilled($settings['mail_host'] ?? null, (string) config('mail.mailers.smtp.host', '127.0.0.1'));
        $smtpPort = (int) $this->firstFilled($settings['mail_port'] ?? null, (string) config('mail.mailers.smtp.port', 2525));
        $smtpUser = $this->nullableOrFallback($settings['mail_username'] ?? null, config('mail.mailers.smtp.username'));
        $smtpPass = $this->nullableOrFallback($settings['mail_password'] ?? null, config('mail.mailers.smtp.password'));
        $fromAddress = $this->firstFilled($settings['mail_from_address'] ?? null, (string) config('mail.from.address', 'hello@example.com'));
        $fromName = $this->firstFilled($settings['mail_from_name'] ?? null, (string) config('mail.from.name', 'Khayma'));

        config([
            'mail.default' => $defaultMailer,
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPass,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);

        if (app()->bound('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
    }

    private function firstFilled(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : $fallback;
    }

    private function nullableOrFallback(?string $value, mixed $fallback): mixed
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : $fallback;
    }
}
