<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Ormawa;

class OrmawaUserSeeder extends Seeder
{
    public function run(): void
    {
        $ormawa = Ormawa::first();
        if (! $ormawa) return;

        $users = User::all();

        foreach ($users as $user) {
            DB::table('ormawa_user')->updateOrInsert(
                [
                    'user_id'  => $user->id,
                    'ormawaID' => $ormawa->id,
                ],
                []
            );
        }
    }
}
