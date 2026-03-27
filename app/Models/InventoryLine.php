<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    protected $fillable = [
        'inventory_id',
        'product_id',
        'system_quantity',
        'physical_quantity',
        'gap',
        'notes',
    ];

    protected $casts = [
        'system_quantity'   => 'decimal:2',
        'physical_quantity' => 'decimal:2',
        'gap'               => 'decimal:2',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
