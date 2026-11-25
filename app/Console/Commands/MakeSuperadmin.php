<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/** Jadikan akun sebagai pengelola NetBill (akses panel /platform). */
class MakeSuperadmin extends Command
{
    protected $signature = 'netbill:superadmin {email} {--revoke : Cabut akses pengelola}';

    protected $description = 'Beri / cabut akses panel pengelola NetBill untuk sebuah akun';

    public function handle(): int
    {
        $user = User::where('email', strtolower(trim($this->argument('email'))))->first();
        if (!$user) {
            $this->error('Akun dengan email itu tidak ditemukan.');

            return self::FAILURE;
        }

        $user->forceFill(['is_superadmin' => !$this->option('revoke')])->save();
        $this->info($this->option('revoke') ? "Akses pengelola {$user->name} dicabut." : "{$user->name} sekarang pengelola NetBill.");

        return self::SUCCESS;
    }
}
