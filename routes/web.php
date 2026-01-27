<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']); 
});

Route::middleware('auth')->group(function () {
    // Jalur ke Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Jalur untuk Logout (Wajib POST demi keamanan)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// === BAGIAN TIKET ===
    
    // 1. Untuk MENAMPILKAN Form (sudah ada)
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');

    // 2. Untuk MENYIMPAN Data (INI YANG KURANG)
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store'); // <--- TAMBAHKAN INI