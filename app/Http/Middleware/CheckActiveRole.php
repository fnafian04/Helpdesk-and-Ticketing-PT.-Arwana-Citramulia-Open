<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk mengecek apakah active role user (dari token) sesuai dengan yang diizinkan.
 * 
 * Usage: middleware('active_role:helpdesk|technician')
 * Cek apakah active role (yang dipilih saat login) termasuk dalam daftar role yang diizinkan.
 */
class CheckActiveRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Parse roles (bisa dipisahkan | atau comma)
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode('|', $role));
        }

        $activeRole = $user->activeRole();

        if (!$activeRole || !in_array($activeRole, $allowedRoles)) {
            return response()->json([
                'message' => 'Akses ditolak. Role aktif Anda (' . ($activeRole ?? 'none') . ') tidak memiliki izin untuk mengakses fitur ini.',
                'active_role' => $activeRole,
                'required_roles' => $allowedRoles,
            ], 403);
        }

        return $next($request);
    }
}
