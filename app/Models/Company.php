<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasAuditLog;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo_url',
        'sector',
        'ninea',
        'currency',
        'timezone',
        'primary_color',
        'secondary_color',
        'subscription_status',
        'trial_ends_at',
        'is_active',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'is_active'     => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_users')
                    ->withPivot('role', 'permissions', 'joined_at')
                    ->withTimestamps();
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'company_modules')
                    ->withPivot('activated_at', 'activated_by')
                    ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function depots(): HasMany
    {
        return $this->hasMany(Depot::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // ── Restaurant ────────────────────────────────────────────

    public function restaurantCategories(): HasMany
    {
        return $this->hasMany(RestaurantCategory::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function cashSessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ── Quincaillerie ─────────────────────────────────────────

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function supplierReturns(): HasMany
    {
        return $this->hasMany(SupplierReturn::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    // ── Boutique / POS ────────────────────────────────────────

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function loyaltyConfig()
    {
        return $this->hasOne(LoyaltyConfig::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function depotTransfers(): HasMany
    {
        return $this->hasMany(DepotTransfer::class);
    }

    // ── Location ──────────────────────────────────────────────

    public function rentalAssets(): HasMany
    {
        return $this->hasMany(RentalAsset::class);
    }

    public function rentalContracts(): HasMany
    {
        return $this->hasMany(RentalContract::class);
    }

    public function rentalPayments(): HasMany
    {
        return $this->hasMany(RentalPayment::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnTrial($query)
    {
        return $query->where('subscription_status', 'trial')
                     ->where('trial_ends_at', '>=', now());
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function hasModule(string $moduleCode): bool
    {
        return $this->modules()->where('code', $moduleCode)->exists();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
                    ->where('status', 'active')
                    ->where('ends_at', '>=', now())
                    ->latest()
                    ->first();
    }
}
