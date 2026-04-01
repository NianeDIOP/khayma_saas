<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyFile extends Model
{
    protected $fillable = [
        'company_id', 'uploaded_by', 'original_name',
        'disk', 'path', 'mime_type', 'size', 'folder',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' o';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' Ko';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' Mo';
        return round($bytes / 1073741824, 2) . ' Go';
    }

    /**
     * Full URL for the file.
     */
    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);
    }
}
