<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\PayDunyaTransaction;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PayDunyaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ── Page abonnement ───────────────────────────────────────

    public function index()
    {
        $company = app('currentCompany');

        $plans = Plan::active()
            ->orderBy('price_monthly')
            ->get(['id', 'name', 'code', 'price_monthly', 'price_quarterly', 'price_yearly',
                   'max_products', 'max_users', 'max_storage_gb', 'max_transactions_month']);

        $currentSubscription = $company->subscriptions()
            ->with('plan')
            ->orderByDesc('ends_at')
            ->first();

        return inertia('App/Subscription/Index', [
            'plans'               => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    // ── Initier le paiement ───────────────────────────────────

    public function initiate(Request $request, Plan $plan, string $period)
    {
        abort_unless(in_array($period, ['monthly', 'quarterly', 'yearly']), 422, 'Période invalide.');
        abort_unless($plan->is_active, 422, 'Ce plan n\'est plus disponible.');

        $company = app('currentCompany');

        $amount = match ($period) {
            'quarterly' => $plan->price_quarterly,
            'yearly'    => $plan->price_yearly,
            default     => $plan->price_monthly,
        };

        $transaction = PayDunyaTransaction::create([
            'company_id'     => $company->id,
            'plan_id'        => $plan->id,
            'billing_period' => $period,
            'amount'         => $amount,
            'status'         => 'pending',
        ]);

        $returnUrl = route('app.payment.callback', ['_tenant' => $company->slug, 'transaction' => $transaction->id]);
        $cancelUrl = route('app.payment.cancel',   ['_tenant' => $company->slug, 'transaction' => $transaction->id]);
        $ipnUrl    = route('payment.webhook');

        try {
            $result = app(PayDunyaService::class)->createInvoice(
                companyName: $company->name,
                amount:      $amount,
                description: "Abonnement Khayma — {$plan->name} ({$period})",
                returnUrl:   $returnUrl,
                cancelUrl:   $cancelUrl,
                ipnUrl:      $ipnUrl,
            );

            $transaction->update([
                'paydunya_token' => $result['token'],
                'invoice_url'    => $result['invoice_url'],
            ]);

            return redirect($result['invoice_url']);
        } catch (\Throwable $e) {
            $transaction->markFailed();
            Log::error('[Payment] initiate failed', ['error' => $e->getMessage(), 'tx' => $transaction->id]);

            return redirect()
                ->route('app.payment.index', ['_tenant' => $company->slug])
                ->with('error', 'Impossible d\'initier le paiement. Veuillez réessayer.');
        }
    }

    // ── Retour après paiement (redirect PayDunya → app) ──────

    public function callback(Request $request, PayDunyaTransaction $transaction)
    {
        abort_unless(
            $transaction->company_id === app('currentCompany')->id,
            403
        );

        if ($transaction->isSuccess()) {
            return redirect()
                ->route('app.dashboard', ['_tenant' => app('currentCompany')->slug])
                ->with('success', 'Paiement déjà validé. Merci !');
        }

        // Confirmer le statut auprès de PayDunya
        $result = app(PayDunyaService::class)->confirmPayment($transaction->paydunya_token ?? '');

        if ($result['status'] === 'completed') {
            $this->activateSubscription($transaction, $result['reference']);

            return redirect()
                ->route('app.dashboard', ['_tenant' => app('currentCompany')->slug])
                ->with('success', 'Paiement confirmé ! Votre abonnement est actif.');
        }

        $transaction->markFailed();

        return redirect()
            ->route('app.payment.index', ['_tenant' => app('currentCompany')->slug])
            ->with('error', 'Paiement non abouti. Statut : ' . $result['status']);
    }

    // ── Annulation ────────────────────────────────────────────

    public function cancel(PayDunyaTransaction $transaction)
    {
        abort_unless(
            $transaction->company_id === app('currentCompany')->id,
            403
        );

        if ($transaction->isPending()) {
            $transaction->markCancelled();
        }

        return redirect()
            ->route('app.payment.index', ['_tenant' => app('currentCompany')->slug])
            ->with('info', 'Paiement annulé.');
    }

    // ── Webhook IPN PayDunya ──────────────────────────────────

    public function webhook(Request $request)
    {
        // PayDunya envoie le token dans la payload
        $token = $request->input('data.invoice.token') ?? $request->input('token') ?? '';

        if (! $token) {
            Log::warning('[PayDunya IPN] Missing token', ['payload' => $request->all()]);
            return response()->json(['status' => 'error', 'message' => 'Missing token'], 400);
        }

        $transaction = PayDunyaTransaction::where('paydunya_token', $token)->first();

        if (! $transaction) {
            Log::warning('[PayDunya IPN] Transaction not found', ['token' => $token]);
            return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
        }

        if ($transaction->isSuccess()) {
            return response()->json(['status' => 'ok', 'message' => 'Already processed']);
        }

        $result = app(PayDunyaService::class)->confirmPayment($token);

        if ($result['status'] === 'completed') {
            $this->activateSubscription($transaction, $result['reference']);
        }

        return response()->json(['status' => 'ok']);
    }

    // ── Internals ─────────────────────────────────────────────

    private function activateSubscription(PayDunyaTransaction $transaction, string $reference): void
    {
        DB::transaction(function () use ($transaction, $reference) {
            $transaction->markSuccess($reference);

            $plan   = $transaction->plan;
            $months = match ($transaction->billing_period) {
                'quarterly' => 3,
                'yearly'    => 12,
                default     => 1,
            };

            $endsAt = now()->addMonths($months);

            Subscription::create([
                'company_id'        => $transaction->company_id,
                'plan_id'           => $transaction->plan_id,
                'status'            => 'active',
                'billing_period'    => $transaction->billing_period,
                'amount_paid'       => $transaction->amount,
                'payment_reference' => $reference,
                'starts_at'         => now(),
                'ends_at'           => $endsAt,
            ]);

            $transaction->company->update(['subscription_status' => 'active']);
        });
    }
}
