<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userData = [
            [
                'name'     => 'Danis',
                'username' => 'danis',
                'email'    => 'm.daniswara.m@gmail.com',
                'password' => Hash::make('123456'),
                'role'     => 'admin',
                'ormawaID' => null,
            ],
            [
                'name'     => 'Kato',
                'username' => 'kato',
                'email'    => 'kato@gmail.com',
                'password' => Hash::make('123456'),
                'role'     => 'user',
                'ormawaID' => null,
            ],
            [
                'name'     => 'Riham',
                'username' => 'riham',
                'email'    => 'riham@gmail.com',
                'password' => Hash::make('123456'),
                'role'     => 'user',
                'ormawaID' => null,
            ],
        ];

        foreach ($userData as $val) {
            User::updateOrCreate(
                ['email' => $val['email']],
                $val
            );
        }
    }
}
