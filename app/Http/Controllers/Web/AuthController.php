<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Wajib pakai ini
use App\Models\User;

class AuthController extends Controller
{
    // Tampilkan Form Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Tampilkan Form Register
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // PROSES LOGIN YANG BENAR (Session Based)
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => ['required', 'email'], // Pastikan name di blade adalah 'email'
            'password' => ['required'],
        ]);

        // 2. Cek User di Database & Buat Session Standar
        // Auth::attempt otomatis mengenkripsi password & mencocokkan
        if (Auth::attempt($credentials)) {
            
            // Regenerasi Session ID (Keamanan)
            $request->session()->regenerate();

            // Return Sukses ke JS
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil!',
                'redirect_url' => route('dashboard') // Nanti JS yang akan redirect
            ]);
        }

        // 3. Jika Gagal
        return response()->json([
            'status' => 'error',
            'message' => 'Email atau password salah.',
        ], 401);
    }

    // PROSES LOGOUT
    public function logout(Request $request)
    {
        Auth::logout(); // Hapus session standar

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // Balik ke halaman login
    }
}