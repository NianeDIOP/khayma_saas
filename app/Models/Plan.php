<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'max_products',
        'max_users',
        'max_storage_gb',
        'max_transactions_month',
        'api_rate_limit',
        'price_monthly',
        'price_quarterly',
        'price_yearly',
        'is_active',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'price_monthly'          => 'integer',
        'price_quarterly'        => 'integer',
        'price_yearly'           => 'integer',
        'max_products'           => 'integer',
        'max_users'              => 'integer',
        'max_storage_gb'         => 'integer',
        'max_transactions_month' => 'integer',
        'api_rate_limit'         => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function formattedPrice(string $period = 'monthly'): string
    {
        $price = match($period) {
            'quarterly' => $this->price_quarterly,
            'yearly'    => $this->price_yearly,
            default     => $this->price_monthly,
        };
        return number_format($price, 0, '.', ' ') . ' XOF';
    }
}
