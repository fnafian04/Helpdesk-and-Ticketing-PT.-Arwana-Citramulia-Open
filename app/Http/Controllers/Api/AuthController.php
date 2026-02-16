<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Services\Auth\AuthCrudService;
use App\Http\Services\Auth\AuthQueryService;
use App\Http\Services\Auth\AuthValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private AuthCrudService $crudService;
    private AuthQueryService $queryService;
    private AuthValidationService $validationService;

    public function __construct(
        AuthCrudService $crudService,
        AuthQueryService $queryService,
        AuthValidationService $validationService
    )
    {
        $this->crudService = $crudService;
        $this->queryService = $queryService;
        $this->validationService = $validationService;
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = $this->crudService->registerUser($validated);

        // Kirim email verifikasi hanya jika fitur enabled
        if (config('emailverification.enabled')) {
            $user->sendEmailVerificationNotification();
        }

        $token = $user->createToken('auth_token:requester')->plainTextToken;
        $userData = $this->queryService->getCurrentUser($user, 'requester');

        return response()->json([
            'message' => 'Register success',
            'user' => $userData['user'],
            'active_role' => 'requester',
            'all_roles' => ['requester'],
            'permissions' => $userData['permissions'],
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $loginField = $this->queryService->getLoginField($validated['login']);

        $user = $this->queryService->authenticateUser($loginField, $validated['login'], $validated['password']);

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$this->queryService->isUserActive($user)) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact administrator.'
            ], 403);
        }

        $userRoles = $user->getRoleNames()->toArray();
        $selectedRole = $validated['role'] ?? null;

        // Jika user punya lebih dari 1 role dan belum memilih role
        if (count($userRoles) > 1 && !$selectedRole) {
            return response()->json([
                'message' => 'Akun ini memiliki beberapa role. Silakan pilih role untuk login.',
                'role_selection_required' => true,
                'available_roles' => $userRoles,
            ], 300);
        }

        // Jika user punya 1 role, otomatis pakai role itu
        if (count($userRoles) === 1) {
            $selectedRole = $userRoles[0];
        }

        // Validasi: role yang dipilih harus dimiliki user
        if ($selectedRole && !in_array($selectedRole, $userRoles)) {
            return response()->json([
                'message' => 'Anda tidak memiliki role tersebut.',
                'available_roles' => $userRoles,
            ], 422);
        }

        $user->tokens()->delete();

        // Simpan active role di token name: "auth_token:{role}"
        $token = $user->createToken('auth_token:' . $selectedRole)->plainTextToken;

        $userData = $this->queryService->getCurrentUser($user, $selectedRole);

        return response()->json([
            'message' => 'Login success',
            'user' => $userData['user'],
            'active_role' => $selectedRole,
            'all_roles' => $userRoles,
            'permissions' => $userData['permissions'],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $this->crudService->logoutUser($request->user()->currentAccessToken());

        return response()->json([
            'message' => 'Logout success'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $activeRole = $user->activeRole();
        $userData = $this->queryService->getCurrentUser($user, $activeRole);

        return response()->json($userData);
    }

    public function validateToken(Request $request)
    {
        $user = $request->user();
        $activeRole = $user->activeRole();
        $userData = $this->queryService->getCurrentUser($user, $activeRole);

        return response()->json([
            'message' => 'Token is valid',
            'valid' => true,
            'user' => $userData['user'],
            'active_role' => $activeRole,
            'all_roles' => $user->getRoleNames(),
            'permissions' => $userData['permissions'],
        ]);
    }

    /**
     * POST /api/switch-role
     * Ganti active role tanpa login ulang (untuk user dengan multiple roles)
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:master-admin,helpdesk,technician,requester',
        ], [
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role harus salah satu dari: master-admin, helpdesk, technician, requester',
        ]);

        $user = $request->user();
        $newRole = $request->input('role');

        // Validasi user memiliki role tersebut
        if (!$user->hasRole($newRole)) {
            return response()->json([
                'message' => 'Anda tidak memiliki role tersebut.',
                'available_roles' => $user->getRoleNames(),
            ], 422);
        }

        // Hapus semua token lama dan buat token baru dengan active role
        $user->tokens()->delete();
        $token = $user->createToken('auth_token:' . $newRole)->plainTextToken;

        $userData = $this->queryService->getCurrentUser($user, $newRole);

        return response()->json([
            'message' => 'Role berhasil diubah ke ' . $newRole,
            'user' => $userData['user'],
            'active_role' => $newRole,
            'all_roles' => $user->getRoleNames(),
            'permissions' => $userData['permissions'],
            'token' => $token,
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai',
                'errors' => [
                    'old_password' => ['Password lama tidak sesuai'],
                ],
            ], 422);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken instanceof PersonalAccessToken) {
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        }

        return response()->json([
            'message' => 'Password berhasil diperbarui',
        ]);
    }
}