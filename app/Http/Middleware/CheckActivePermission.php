<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk mengecek apakah active role user memiliki permission tertentu.
 * 
 * Usage: middleware('active_permission:ticket.assign')
 * Berbeda dengan Spatie permission middleware yang cek semua role,
 * ini hanya mengecek permission dari active role yang dipilih saat login.
 */
class CheckActivePermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $activeRole = $user->activeRole();

        if (!$activeRole) {
            return response()->json([
                'message' => 'Active role tidak ditemukan. Silakan login ulang.',
            ], 403);
        }

        // Ambil permission dari active role saja
        $role = Role::findByName($activeRole, 'web');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Parse permissions (bisa dipisahkan | atau comma)
        $requiredPermissions = [];
        foreach ($permissions as $permission) {
            $requiredPermissions = array_merge($requiredPermissions, explode('|', $permission));
        }

        // Cek apakah active role memiliki salah satu permission yang dibutuhkan
        $hasPermission = !empty(array_intersect($requiredPermissions, $rolePermissions));

        if (!$hasPermission) {
            return response()->json([
                'message' => 'Akses ditolak. Role aktif Anda (' . $activeRole . ') tidak memiliki permission untuk mengakses fitur ini.',
                'active_role' => $activeRole,
                'required_permissions' => $requiredPermissions,
            ], 403);
        }

        return $next($request);
    }
}
