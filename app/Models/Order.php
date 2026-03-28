<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, \App\Traits\HasAuditLog;

    protected $fillable = [
        'company_id',
        'reference',
        'type',
        'table_number',
        'delivery_address',
        'delivery_person',
        'customer_name',
        'service_id',
        'cash_session_id',
        'user_id',
        'status',
        'cancel_reason',
        'subtotal',
        'discount_amount',
        'total',
        'payment_method',
        'payment_status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_at'         => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate a unique order reference.
     */
    public static function generateReference(int $companyId): string
    {
        $date = now()->format('Ymd');
        $last = static::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->count();

        return 'CMD-' . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
