<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'product_id', 'name', 'sku', 'barcode',
        'price_override', 'purchase_price_override',
        'attributes', 'is_active',
    ];

    protected $casts = [
        'price_override'          => 'decimal:2',
        'purchase_price_override' => 'decimal:2',
        'attributes'              => 'array',
        'is_active'               => 'boolean',
    ];

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(VariantStockItem::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->price_override ?? $this->product->selling_price;
    }

    public function getEffectivePurchasePriceAttribute(): float
    {
        return $this->purchase_price_override ?? $this->product->purchase_price;
    }
}
