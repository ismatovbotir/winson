<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates/updates the /admin login from ADMIN_NAME / ADMIN_EMAIL /
 * ADMIN_PASSWORD in .env (see config/admin.php). Skipped if those are unset.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('AdminUserSeeder skipped: set ADMIN_EMAIL and ADMIN_PASSWORD in .env.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => config('admin.name'), 'password' => $password]
        );

        $this->command?->info("Admin user ready: {$email}");
    }
}
