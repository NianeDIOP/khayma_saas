<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'min_points', 'bonus_multiplier', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_points'       => 'integer',
            'bonus_multiplier' => 'decimal:2',
            'sort_order'       => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Given a point balance, resolve the matching tier for a company.
     */
    public static function resolveForPoints(int $companyId, int $points): ?self
    {
        return static::where('company_id', $companyId)
            ->where('min_points', '<=', $points)
            ->orderByDesc('min_points')
            ->first();
    }
}
