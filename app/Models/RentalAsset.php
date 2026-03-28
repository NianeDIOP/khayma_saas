<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalAsset extends Model
{
    use HasFactory, \App\Traits\HasAuditLog;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'type',
        'daily_rate',
        'monthly_rate',
        'status',
        'characteristics',
        'images',
        'documents',
        'inspection_notes',
        'is_active',
    ];

    protected $casts = [
        'daily_rate'      => 'decimal:2',
        'monthly_rate'    => 'decimal:2',
        'characteristics' => 'array',
        'images'          => 'array',
        'documents'       => 'array',
        'is_active'       => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(RentalContract::class);
    }

    public function activeContract()
    {
        return $this->contracts()->where('status', 'active')->latest()->first();
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
