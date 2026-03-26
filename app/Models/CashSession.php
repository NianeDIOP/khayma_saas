<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'service_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'notes',
    ];

    protected $casts = [
        'opened_at'      => 'datetime',
        'closed_at'      => 'datetime',
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isOpen(): bool
    {
        return is_null($this->closed_at);
    }
}
