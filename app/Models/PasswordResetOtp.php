<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $table = 'password_reset_otps';

    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
        'is_used',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
        'attempts' => 'integer',
    ];

    /**
     * Cek apakah OTP sudah expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Cek apakah OTP masih valid (belum expired & belum dipakai & attempts < max)
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired() && $this->attempts < 5;
    }

    /**
     * Tandai OTP sebagai sudah dipakai
     */
    public function markAsUsed(): void
    {
        $this->is_used = true;
        $this->save();
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Scope: OTP yang masih aktif untuk email tertentu
     */
    public function scopeActiveForEmail($query, string $email)
    {
        return $query->where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->where('attempts', '<', 5);
    }

    /**
     * Invalidate semua OTP lama untuk email tertentu
     */
    public static function invalidateAllForEmail(string $email): void
    {
        static::where('email', $email)
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }
}
