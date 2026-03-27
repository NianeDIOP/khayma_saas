<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantStockItem extends Model
{
    protected $fillable = [
        'company_id', 'product_variant_id', 'depot_id', 'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
}
