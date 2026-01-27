<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

// Redirect halaman utama ke login dulu (sementara)
Route::get('/', function () {
    return redirect()->route('login');
});

// GROUP: Khusus user yang BELUM LOGIN (Guest)
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']); // Action form login

    // Register Routes
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']); // Action form register
});

// GROUP: Khusus user yang SUDAH LOGIN (Auth)
Route::middleware('auth')->group(function () {
    // Dashboard sementara
    Route::get('/dashboard', function () {
        return "Selamat Datang di Dashboard Helpdesk!"; // Nanti kita ganti view dashboard
    })->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
