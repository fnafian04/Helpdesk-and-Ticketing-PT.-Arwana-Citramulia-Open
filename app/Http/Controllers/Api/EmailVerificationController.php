<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class EmailVerificationController extends Controller
{
    /**
     * Verifikasi email user berdasarkan signed URL.
     * Endpoint ini dipanggil dari frontend SPA setelah user klik link di email.
     *
     * GET /api/email/verify/{id}/{hash}
     */
    public function verify(Request $request, $id, $hash): JsonResponse
    {
        // Validasi signed URL (termasuk expiration)
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Link verifikasi tidak valid atau sudah kedaluwarsa.',
                'verified' => false,
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
                'verified' => false,
            ], 404);
        }

        // Validasi hash email
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'message' => 'Hash verifikasi tidak valid.',
                'verified' => false,
            ], 403);
        }

        // Jika sudah terverifikasi sebelumnya
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah terverifikasi sebelumnya.',
                'verified' => true,
            ]);
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email berhasil diverifikasi.',
            'verified' => true,
        ]);
    }

    /**
     * Kirim ulang email verifikasi.
     * User harus sudah login (auth:sanctum).
     *
     * POST /api/email/verification-notification
     */
    public function send(Request $request): JsonResponse
    {
        if (!config('emailverification.enabled')) {
            return response()->json([
                'message' => 'Fitur email verification sedang dinonaktifkan.',
                'verification_enabled' => false,
            ]);
        }

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah terverifikasi.',
                'verified' => true,
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Link verifikasi telah dikirim ke email Anda.',
        ]);
    }

    /**
     * Cek status verifikasi email user.
     *
     * GET /api/email/verification-status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $verificationEnabled = (bool) config('emailverification.enabled');

        return response()->json([
            'verified' => !$verificationEnabled || $user->hasVerifiedEmail(),
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'verification_enabled' => $verificationEnabled,
        ]);
    }
}
