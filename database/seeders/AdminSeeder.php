<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('sembako.admin');

        if (empty($cfg['email']) || empty($cfg['password'])) {
            $this->command->error('ADMIN_EMAIL dan ADMIN_PASSWORD wajib diisi di .env');

            return;
        }

        $admin = User::firstOrNew(['email' => $cfg['email']]);

        // forceFill karena 'role' sengaja tidak mass-assignable. Password di-hash bcrypt oleh cast.
        $admin->forceFill([
            'name'      => $cfg['name'],
            'password'  => $cfg['password'],
            'role'      => 'admin',
            'is_active' => true,
        ])->save();

        $this->command->info("Admin siap: {$cfg['email']}");
    }
}