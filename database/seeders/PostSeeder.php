<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Ormawa;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::where('role', 'admin')->first();
        $ormawa = Ormawa::first();

        if (! $admin || ! $ormawa) return;

        Post::updateOrCreate(
            ['title' => 'Open Recruitment Pengurus'],
            [
                'userID'      => $admin->id,
                'ormawaID'    => $ormawa->id,
                'description' => 'Pendaftaran pengurus baru periode 2025.',
                'posterPath'  => null,
                'status'      => 'published',
            ]
        );

        Post::updateOrCreate(
            ['title' => 'Pelatihan Internal'],
            [
                'userID'      => $admin->id,
                'ormawaID'    => $ormawa->id,
                'description' => 'Pelatihan internal anggota ormawa.',
                'posterPath'  => null,
                'status'      => 'draft',
            ]
        );
    }
}
