<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya akun admin. Data harga TIDAK di-seed: diisi `php artisan fetch:sembako`.
        $this->call(AdminSeeder::class);
    }
}