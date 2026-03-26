<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'restaurant_category_id',
        'name',
        'description',
        'price',
        'image_url',
        'is_available',
        'available_morning',
        'available_noon',
        'available_evening',
        'is_additional',
        'promo_price',
        'promo_start',
        'promo_end',
        'sort_order',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'promo_price'       => 'decimal:2',
        'promo_start'       => 'date',
        'promo_end'         => 'date',
        'is_available'      => 'boolean',
        'available_morning' => 'boolean',
        'available_noon'    => 'boolean',
        'available_evening' => 'boolean',
        'is_additional'     => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RestaurantCategory::class, 'restaurant_category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Returns the effective price (promo if active, otherwise base price).
     */
    public function getEffectivePriceAttribute(): string
    {
        if ($this->promo_price && $this->promo_start && $this->promo_end) {
            $today = now()->toDateString();
            if ($today >= $this->promo_start->toDateString() && $today <= $this->promo_end->toDateString()) {
                return $this->promo_price;
            }
        }

        return $this->price;
    }
}
