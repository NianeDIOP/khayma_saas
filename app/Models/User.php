<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_super_admin'    => 'boolean',
        ];
    }

    // ── Relations ─────────────────────────────────────────────

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_users')
                    ->withPivot('role', 'permissions', 'joined_at')
                    ->withTimestamps();
    }

    // ── Helpers ───────────────────────────────────────────────

    public function currentCompany(): ?Company
    {
        // Récupère la première entreprise active de l'utilisateur
        // Phase 1C ajoutera la sélection multi-entreprise via session
        return $this->companies()->first();
    }

    public function roleInCompany(Company $company): ?string
    {
        $pivot = $company->users()->where('user_id', $this->id)->first();
        return $pivot?->pivot->role;
    }

    public function permissionsInCompany(Company $company): ?array
    {
        $pivot = $company->users()->where('user_id', $this->id)->first();
        $perms = $pivot?->pivot->permissions;
        if (is_string($perms)) {
            return json_decode($perms, true);
        }
        return $perms;
    }
}
