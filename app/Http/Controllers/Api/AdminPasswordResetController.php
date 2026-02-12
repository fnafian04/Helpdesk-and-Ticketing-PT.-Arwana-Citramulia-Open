<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpResetPasswordRequest;
use App\Http\Services\Auth\AdminPasswordResetService;

class AdminPasswordResetController extends Controller
{
    private AdminPasswordResetService $resetService;

    public function __construct(AdminPasswordResetService $resetService)
    {
        $this->resetService = $resetService;
    }

    /**
     * Step 1: Request OTP - kirim kode verifikasi ke email master admin
     * POST /api/admin/forgot-password
     * Body: { "email": "admin@example.com" }
     */
    public function requestOtp(RequestOtpRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];

        // Cek apakah email milik master admin
        $user = $this->resetService->findMasterAdmin($email);

        // Selalu return success message untuk keamanan (tidak expose apakah email terdaftar)
        if (!$user) {
            return response()->json([
                'message' => 'Jika email terdaftar sebagai Master Admin, kode verifikasi akan dikirim.',
            ]);
        }

        // Cek cooldown
        if ($this->resetService->isInCooldown($email)) {
            $remaining = $this->resetService->getRemainingCooldown($email);
            return response()->json([
                'message' => "Mohon tunggu {$remaining} detik sebelum meminta kode baru.",
                'retry_after' => $remaining,
            ], 429);
        }

        try {
            $result = $this->resetService->generateAndSendOtp($user);

            return response()->json([
                'message' => 'Kode verifikasi telah dikirim ke email Anda.',
                'data' => [
                    'expiration_minutes' => $result['expiration_minutes'],
                    'max_attempts' => $result['max_attempts'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim kode verifikasi. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Step 2: Verify OTP only (cek validitas sebelum tampilkan form password)
     * POST /api/admin/verify-otp
     * Body: { "email": "...", "otp_code": "123456" }
     */
    public function verifyOtp(RequestOtpRequest $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $result = $this->resetService->verifyOtpOnly(
            $request->input('email'),
            $request->input('otp_code')
        );

        if (!$result['success']) {
            $statusCode = match ($result['code']) {
                'max_attempts_exceeded' => 429,
                'invalid_otp' => 422,
                default => 400,
            };

            $response = ['message' => $result['message']];

            if (isset($result['remaining_attempts'])) {
                $response['remaining_attempts'] = $result['remaining_attempts'];
            }

            return response()->json($response, $statusCode);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    /**
     * Step 3: Verify OTP & Reset Password
     * POST /api/admin/reset-password
     * Body: { "email": "...", "otp_code": "123456", "new_password": "...", "new_password_confirmation": "..." }
     */
    public function resetPassword(VerifyOtpResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $result = $this->resetService->verifyOtpAndResetPassword(
            $validated['email'],
            $validated['otp_code'],
            $validated['new_password']
        );

        if (!$result['success']) {
            $statusCode = match ($result['code']) {
                'max_attempts_exceeded' => 429,
                'invalid_otp' => 422,
                default => 400,
            };

            $response = [
                'message' => $result['message'],
            ];

            if (isset($result['remaining_attempts'])) {
                $response['remaining_attempts'] = $result['remaining_attempts'];
            }

            return response()->json($response, $statusCode);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
