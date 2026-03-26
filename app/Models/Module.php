<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'installation_fee',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'installation_fee' => 'integer',
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_modules')
                    ->withPivot('activated_at', 'activated_by')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
