<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'points_per_amount', 'amount_per_point',
        'redemption_threshold', 'redemption_value', 'is_active',
    ];

    protected $casts = [
        'points_per_amount'    => 'integer',
        'amount_per_point'     => 'decimal:2',
        'redemption_threshold' => 'integer',
        'redemption_value'     => 'decimal:2',
        'is_active'            => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function calculatePoints(float $amount): int
    {
        if ($this->amount_per_point <= 0) {
            return 0;
        }

        return (int) floor($amount / $this->amount_per_point) * $this->points_per_amount;
    }

    public function calculateRedemptionValue(int $points): float
    {
        if ($this->redemption_threshold <= 0) {
            return 0;
        }

        $redeemableSets = (int) floor($points / $this->redemption_threshold);

        return $redeemableSets * $this->redemption_value;
    }
}
