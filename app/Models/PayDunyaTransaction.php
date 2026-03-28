<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayDunyaTransaction extends Model
{
    protected $table = 'paydunya_transactions';

    protected $fillable = [
        'company_id',
        'plan_id',
        'billing_period',
        'amount',
        'status',
        'paydunya_token',
        'invoice_url',
        'payment_reference',
        'metadata',
    ];

    protected $casts = [
        'amount'   => 'integer',
        'metadata' => 'array',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function markSuccess(string $reference): void
    {
        $this->update(['status' => 'success', 'payment_reference' => $reference]);
    }

    public function markFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function markCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
