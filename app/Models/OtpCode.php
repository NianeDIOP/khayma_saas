<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['phone', 'code', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Generate a 6-digit OTP for a phone number.
     */
    public static function generate(string $phone, int $expiryMinutes = 5): self
    {
        // Invalidate previous unused codes for this phone
        static::where('phone', $phone)->whereNull('used_at')->delete();

        return static::create([
            'phone'      => $phone,
            'code'       => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }
}
