<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Intégration PayDunya — agrégateur de paiement sénégalais.
 *
 * Modes :
 *  - log  : les appels sont loggués, aucun appel réel (développement)
 *  - fake : renvoie des données factices sans log (tests)
 *  - api  : appels HTTP réels vers l'API PayDunya (production/staging)
 *
 * Documentation : https://paydunya.com/developers/
 */
class PayDunyaService
{
    private const SANDBOX_BASE = 'https://app.paydunya.com/sandbox-api/v1';
    private const LIVE_BASE    = 'https://app.paydunya.com/api/v1';

    // ── Public API ─────────────────────────────────────────────

    /**
     * Créer une facture PayDunya et retourner le token + l'URL de paiement.
     *
     * @return array{token:string,invoice_url:string}
     */
    public function createInvoice(
        string $companyName,
        int    $amount,
        string $description,
        string $returnUrl,
        string $cancelUrl,
        string $ipnUrl,
    ): array {
        $settings = $this->resolveSettings();
        $mode     = $settings['mode'];

        if ($mode === 'fake') {
            return [
                'token'       => 'fake_token_' . uniqid(),
                'invoice_url' => 'https://app.paydunya.com/sandbox/checkout-invoice/confirm/fake',
            ];
        }

        if ($mode === 'log') {
            $fakeToken = 'log_token_' . uniqid();
            Log::info('[PayDunya:LOG] createInvoice', [
                'company'     => $companyName,
                'amount'      => $amount,
                'description' => $description,
            ]);
            return [
                'token'       => $fakeToken,
                'invoice_url' => 'https://app.paydunya.com/sandbox/checkout-invoice/confirm/' . $fakeToken,
            ];
        }

        // mode === 'api'
        $base = $settings['paydunya_mode'] === 'live' ? self::LIVE_BASE : self::SANDBOX_BASE;

        $payload = [
            'invoice'          => [
                'total_amount' => $amount,
                'description'  => $description,
            ],
            'store'            => [
                'name'     => $companyName,
                'website'  => $returnUrl,
                'logo_url' => '',
            ],
            'actions'          => [
                'cancel_url'   => $cancelUrl,
                'return_url'   => $returnUrl,
                'callback_url' => $ipnUrl,
            ],
            'custom_data'      => [],
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->headers($settings))
                ->post($base . '/checkout-invoice/create', $payload);

            if (! $response->successful() || ($response->json('response_code') !== '00')) {
                Log::error('[PayDunya] createInvoice failed', ['body' => $response->body()]);
                throw new \RuntimeException('PayDunya invoice creation failed: ' . $response->body());
            }

            $token = $response->json('token');

            return [
                'token'       => $token,
                'invoice_url' => $response->json('description'), // PayDunya returns the checkout URL in "description"
            ];
        } catch (\Throwable $e) {
            Log::error('[PayDunya] HTTP error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Confirmer le statut d'un paiement depuis le token.
     *
     * @return array{status:string,reference:string}
     */
    public function confirmPayment(string $token): array
    {
        $settings = $this->resolveSettings();
        $mode     = $settings['mode'];

        if ($mode === 'fake') {
            return ['status' => 'completed', 'reference' => 'FAKE_REF_' . strtoupper(uniqid())];
        }

        if ($mode === 'log') {
            Log::info('[PayDunya:LOG] confirmPayment', ['token' => $token]);
            return ['status' => 'completed', 'reference' => 'LOG_REF_' . strtoupper(uniqid())];
        }

        $base = $settings['paydunya_mode'] === 'live' ? self::LIVE_BASE : self::SANDBOX_BASE;

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->headers($settings))
                ->get($base . '/checkout-invoice/confirm/' . $token);

            $data   = $response->json();
            $status = $data['status'] ?? 'failed';

            return [
                'status'    => $status,
                'reference' => $data['receipt_url'] ?? $data['invoice']['invoice_data']['invoice_number'] ?? $token,
            ];
        } catch (\Throwable $e) {
            Log::error('[PayDunya] confirmPayment error', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'reference' => ''];
        }
    }

    // ── Helpers ────────────────────────────────────────────────

    /**
     * @return array<string,string>
     */
    private function headers(array $settings): array
    {
        return [
            'PAYDUNYA-MASTER-KEY'  => $settings['master_key'],
            'PAYDUNYA-PRIVATE-KEY' => $settings['private_key'],
            'PAYDUNYA-TOKEN'       => $settings['token'],
            'Content-Type'         => 'application/json',
        ];
    }

    /**
     * @return array{mode:string,paydunya_mode:string,master_key:string,private_key:string,token:string}
     */
    private function resolveSettings(): array
    {
        $defaults = [
            'mode'         => (string) config('services.paydunya.mode', 'log'),
            'paydunya_mode'=> (string) config('services.paydunya.paydunya_mode', 'test'),
            'master_key'   => (string) config('services.paydunya.master_key', ''),
            'private_key'  => (string) config('services.paydunya.private_key', ''),
            'token'        => (string) config('services.paydunya.token', ''),
        ];

        try {
            $stored = PlatformSetting::getMany([
                'paydunya_mode',
                'paydunya_master_key',
                'paydunya_private_key',
                'paydunya_token',
                'paydunya_env',
            ]);
        } catch (\Throwable) {
            return $defaults;
        }

        return [
            'mode'          => $this->firstFilled($stored['paydunya_mode'] ?? null, $defaults['mode']),
            'paydunya_mode' => $this->firstFilled($stored['paydunya_env'] ?? null, $defaults['paydunya_mode']),
            'master_key'    => $this->firstFilled($stored['paydunya_master_key'] ?? null, $defaults['master_key']),
            'private_key'   => $this->firstFilled($stored['paydunya_private_key'] ?? null, $defaults['private_key']),
            'token'         => $this->firstFilled($stored['paydunya_token'] ?? null, $defaults['token']),
        ];
    }

    private function firstFilled(?string $value, string $fallback): string
    {
        $v = trim((string) $value);
        return $v !== '' ? $v : $fallback;
    }
}
