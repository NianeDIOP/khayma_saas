<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'title',
        'body',
        'channel',
        'is_read',
        'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // ── Relations ─────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helper ────────────────────────────────────────────────

    public static function send(
        int $userId,
        string $title,
        ?string $body = null,
        string $type = 'info',
        ?int $companyId = null,
    ): self {
        return self::create([
            'company_id' => $companyId ?? (app()->bound('currentCompany') ? app('currentCompany')->id : null),
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'channel'    => 'in_app',
            'sent_at'    => now(),
        ]);
    }
}
