<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
// use App\Models\Role; // Aktifkan jika sudah ada Model Role
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Tampilkan Form Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Tampilkan Form Register
    public function showRegisterForm()
    {
        // Ambil semua data dari tabel 'departments'
        // Pastikan nama tabel di database benar 'departments' (jamak/plural)
        $departments = DB::table('departments')->get(); 
        
        return view('auth.register', compact('departments'));
    }

    // 3. Proses Login (Logic nanti diisi)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // 4. Proses Register
    public function register(Request $request)
    {
        // 1. Validasi disesuaikan dengan database temanmu
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|numeric|unique:users', // Sesuai $fillable
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'required|exists:departments,id', // Harus pilih departemen valid
        ]);

        // B. Simpan User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
        ]);

        // C. ASSIGN ROLE OTOMATIS ('Requester')
        // Cari ID role 'Requester' dari tabel roles
        $roleRequester = DB::table('roles')->where('name', 'Requester')->first();

        if ($roleRequester) {
            // Masukkan ke tabel pivot (penghubung user & role)
            // Asumsi nama tabel pivotnya standard Laravel: 'role_user'
            // Kolomnya biasanya: user_id, role_id
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $roleRequester->id,
                // 'created_at' => now(), // Uncomment jika tabel pivot ada timestamp
                // 'updated_at' => now(),
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil!');
    }
    
    // 5. Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }//
}
