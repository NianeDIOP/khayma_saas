<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalContract extends Model
{
    use HasFactory, \App\Traits\HasAuditLog;

    protected $fillable = [
        'company_id',
        'rental_asset_id',
        'customer_id',
        'user_id',
        'reference',
        'start_date',
        'end_date',
        'total_amount',
        'deposit_amount',
        'deposit_returned',
        'deposit_returned_amount',
        'payment_frequency',
        'status',
        'conditions',
        'inspection_start',
        'inspection_end',
        'notes',
        'renewed_from_id',
    ];

    protected $casts = [
        'start_date'              => 'date',
        'end_date'                => 'date',
        'total_amount'            => 'decimal:2',
        'deposit_amount'          => 'decimal:2',
        'deposit_returned_amount' => 'decimal:2',
        'deposit_returned'        => 'boolean',
        'inspection_start'        => 'array',
        'inspection_end'          => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rentalAsset(): BelongsTo
    {
        return $this->belongsTo(RentalAsset::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentalPayment::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_id');
    }

    public function renewal(): HasMany
    {
        return $this->hasMany(self::class, 'renewed_from_id');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    public function remainingAmount(): float
    {
        return (float) $this->total_amount - $this->totalPaid();
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
