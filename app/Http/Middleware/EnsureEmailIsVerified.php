<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     * Memastikan user sudah verifikasi email sebelum mengakses endpoint.
     * Mengembalikan JSON response 403 untuk API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass jika email verification dinonaktifkan (development)
        if (!config('emailverification.enabled')) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Alamat email Anda belum diverifikasi. Silakan cek inbox email Anda.',
                'email_verified' => false,
            ], 403);
        }

        return $next($request);
    }
}
