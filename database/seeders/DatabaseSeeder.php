<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Memanggil seeder asli bawaan aplikasi Bos
        $this->call([
            ChartOfAccountSeeder::class,
        ]);
    }
}