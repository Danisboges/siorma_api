<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ormawa;

class AssignUserOrmawaSeeder extends Seeder
{
    public function run(): void
    {
        $ormawa = Ormawa::first();

        if (! $ormawa) {
            return;
        }

        // Admin jadi pengurus utama ormawa
        User::where('role', 'admin')->update([
            'ormawaID' => $ormawa->id,
        ]);

        // User biasa ikut ormawa juga (opsional)
        User::where('role', 'user')->update([
            'ormawaID' => $ormawa->id,
        ]);
    }
}
