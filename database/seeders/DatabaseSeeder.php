<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OrmawaSeeder::class,
            AssignUserOrmawaSeeder::class,
            OrmawaUserSeeder::class,
            PostSeeder::class,
            // RegistrationSeeder::class,
        ]);
    }
}
