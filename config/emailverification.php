<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Verification Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk fitur verifikasi email.
    | Hanya berisi setting yang kemungkinan berubah antar environment.
    |
    */

    // Aktifkan/nonaktifkan fitur email verification
    // Set false untuk bypass saat development
    'enabled' => (bool) env('EMAIL_VERIFICATION_ENABLED', true),

    // URL frontend SPA untuk redirect setelah verifikasi
    'frontend_url' => env('EMAIL_VERIFICATION_FRONTEND_URL', 'http://localhost:8000'),

    // Path di frontend untuk halaman verifikasi (akan ditambahkan query params)
    'frontend_path' => env('EMAIL_VERIFICATION_FRONTEND_PATH', '/email/verify-result'),

    // Durasi link verifikasi valid (dalam menit)
    'expiration' => (int) env('EMAIL_VERIFICATION_EXPIRATION', 30),

];
