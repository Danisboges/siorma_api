<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ormawa;
use App\Models\User;

class OrmawaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        Ormawa::updateOrCreate(
            ['name' => 'Himpunan Mahasiswa Informatika'],
            [
                'user_id'         => $admin?->id,
                'photo_path'      => null,
                'type_ormawa'     => 'Himpunan',
                'category_ormawa' => 'Fakultas',
                'status_oprec'    => 'open',
                'description'     => 'Ormawa Himpunan Mahasiswa Informatika',
            ]
        );

        Ormawa::updateOrCreate(
            ['name' => 'Unit Kegiatan Mahasiswa Basket'],
            [
                'user_id'         => null,
                'photo_path'      => null,
                'type_ormawa'     => 'UKM',
                'category_ormawa' => 'Olahraga',
                'status_oprec'    => 'closed',
                'description'     => 'UKM Basket Universitas',
            ]
        );
    }
}
