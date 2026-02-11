<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserEmail extends Command
{
    /**
     * The name and signature of the console command.
     * Usage:
     *   php artisan user:verify-email user@example.com
     *   php artisan user:verify-email --all
     *
     * @var string
     */
    protected $signature = 'user:verify-email
                            {email? : Email user yang akan diverifikasi}
                            {--all : Verifikasi semua user yang belum verified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifikasi email user secara manual (untuk development)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->verifyAll();
        }

        $email = $this->argument('email');

        if (!$email) {
            $this->error('Masukkan email user atau gunakan --all untuk verifikasi semua.');
            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email '{$email}' tidak ditemukan.");
            return Command::FAILURE;
        }

        if ($user->hasVerifiedEmail()) {
            $this->info("Email '{$email}' sudah terverifikasi sebelumnya.");
            return Command::SUCCESS;
        }

        $user->markEmailAsVerified();
        $this->info("Email '{$email}' berhasil diverifikasi.");

        return Command::SUCCESS;
    }

    /**
     * Verifikasi semua user yang belum verified.
     */
    private function verifyAll(): int
    {
        $users = User::whereNull('email_verified_at')->get();

        if ($users->isEmpty()) {
            $this->info('Semua user sudah terverifikasi.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($users as $user) {
            $user->markEmailAsVerified();
            $count++;
            $this->line("  ✓ {$user->email}");
        }

        $this->info("{$count} user berhasil diverifikasi.");

        return Command::SUCCESS;
    }
}
