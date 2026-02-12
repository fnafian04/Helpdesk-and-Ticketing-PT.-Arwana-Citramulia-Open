<?php

namespace App\Http\Services\Auth;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminPasswordResetService
{
    /**
     * Durasi OTP valid (dalam menit)
     */
    private int $otpExpirationMinutes = 15;

    /**
     * Maksimal percobaan verifikasi OTP
     */
    private int $maxAttempts = 5;

    /**
     * Cooldown antar request OTP (dalam detik)
     */
    private int $cooldownSeconds = 60;

    /**
     * Cari user master-admin berdasarkan email
     */
    public function findMasterAdmin(string $email): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->hasRole('master-admin')) {
            return null;
        }

        return $user;
    }

    /**
     * Cek apakah masih dalam cooldown period
     */
    public function isInCooldown(string $email): bool
    {
        $lastOtp = PasswordResetOtp::where('email', $email)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return false;
        }

        return $lastOtp->created_at->diffInSeconds(now()) < $this->cooldownSeconds;
    }

    /**
     * Hitung sisa cooldown dalam detik
     */
    public function getRemainingCooldown(string $email): int
    {
        $lastOtp = PasswordResetOtp::where('email', $email)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return 0;
        }

        $elapsed = $lastOtp->created_at->diffInSeconds(now());
        return max(0, $this->cooldownSeconds - $elapsed);
    }

    /**
     * Generate dan kirim OTP ke email master admin
     */
    public function generateAndSendOtp(User $user): array
    {
        // Invalidate semua OTP lama
        PasswordResetOtp::invalidateAllForEmail($user->email);

        // Generate kode OTP 6 digit
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan OTP (hashed)
        PasswordResetOtp::create([
            'email' => $user->email,
            'otp_code' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes($this->otpExpirationMinutes),
            'is_used' => false,
            'attempts' => 0,
        ]);

        // Kirim notifikasi email
        $user->notify(new PasswordResetOtpNotification($otpCode, $this->otpExpirationMinutes));

        return [
            'expiration_minutes' => $this->otpExpirationMinutes,
            'max_attempts' => $this->maxAttempts,
        ];
    }

    /**
     * Verifikasi OTP saja (tanpa reset password)
     */
    public function verifyOtpOnly(string $email, string $otpCode): array
    {
        // Cari OTP yang masih aktif
        $otp = PasswordResetOtp::activeForEmail($email)
            ->latest()
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan atau sudah expired. Silakan request kode baru.',
                'code' => 'otp_not_found',
            ];
        }

        // Cek jumlah percobaan
        if ($otp->attempts >= $this->maxAttempts) {
            $otp->markAsUsed();
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Kode OTP telah dinonaktifkan. Silakan request kode baru.',
                'code' => 'max_attempts_exceeded',
            ];
        }

        // Verifikasi kode OTP
        if (!Hash::check($otpCode, $otp->otp_code)) {
            $otp->incrementAttempts();
            $remainingAttempts = $this->maxAttempts - $otp->attempts;

            return [
                'success' => false,
                'message' => "Kode OTP tidak valid. Sisa percobaan: {$remainingAttempts}",
                'code' => 'invalid_otp',
                'remaining_attempts' => $remainingAttempts,
            ];
        }

        return [
            'success' => true,
            'message' => 'Kode OTP valid.',
            'code' => 'valid',
        ];
    }

    /**
     * Verifikasi OTP dan reset password
     */
    public function verifyOtpAndResetPassword(string $email, string $otpCode, string $newPassword): array
    {
        // Cari OTP yang masih aktif
        $otp = PasswordResetOtp::activeForEmail($email)
            ->latest()
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan atau sudah expired. Silakan request kode baru.',
                'code' => 'otp_not_found',
            ];
        }

        // Cek jumlah percobaan
        if ($otp->attempts >= $this->maxAttempts) {
            $otp->markAsUsed();
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Kode OTP telah dinonaktifkan. Silakan request kode baru.',
                'code' => 'max_attempts_exceeded',
            ];
        }

        // Verifikasi kode OTP
        if (!Hash::check($otpCode, $otp->otp_code)) {
            $otp->incrementAttempts();
            $remainingAttempts = $this->maxAttempts - $otp->attempts;

            return [
                'success' => false,
                'message' => "Kode OTP tidak valid. Sisa percobaan: {$remainingAttempts}",
                'code' => 'invalid_otp',
                'remaining_attempts' => $remainingAttempts,
            ];
        }

        // OTP valid - reset password
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan.',
                'code' => 'user_not_found',
            ];
        }

        // Reset password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Hapus semua token (force logout dari semua device)
        $user->tokens()->delete();

        // Tandai OTP sebagai sudah dipakai
        $otp->markAsUsed();

        // Invalidate semua OTP lain untuk email ini
        PasswordResetOtp::invalidateAllForEmail($email);

        return [
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login dengan password baru.',
            'code' => 'success',
        ];
    }

    /**
     * Bersihkan OTP yang sudah expired (untuk scheduled task)
     */
    public function cleanupExpiredOtps(): int
    {
        return PasswordResetOtp::where('expires_at', '<', now())
            ->orWhere('is_used', true)
            ->delete();
    }
}
