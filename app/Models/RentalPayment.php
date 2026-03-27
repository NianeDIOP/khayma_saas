<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'rental_contract_id',
        'due_date',
        'amount',
        'amount_paid',
        'payment_date',
        'method',
        'status',
        'reference',
        'notes',
    ];

    protected $casts = [
        'due_date'      => 'date',
        'payment_date'  => 'date',
        'amount'        => 'decimal:2',
        'amount_paid'   => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rentalContract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
