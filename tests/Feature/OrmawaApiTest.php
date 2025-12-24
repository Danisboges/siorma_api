<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ormawa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class OrmawaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Pakai user seeder Anda (admin: m.daniswara.m@gmail.com)
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    // ==========================
    // PUBLIC ENDPOINTS
    // ==========================

    public function test_public_ormawa_index_returns_200(): void
    {
        $this->getJson('/api/ormawa')
            ->assertStatus(200);
    }

    public function test_public_ormawa_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/ormawa/999999')
            ->assertStatus(404);
    }

    // ==========================
    // ADMIN ENDPOINTS (AUTH + ROLE)
    // ==========================

    public function test_admin_store_requires_authentication(): void
    {
        $this->postJson('/api/admin/ormawa', [])
            ->assertStatus(401);
    }

    public function test_admin_store_returns_403_for_non_admin(): void
    {
        $user = User::where('email', 'kato@gmail.com')->firstOrFail(); // role user
        Sanctum::actingAs($user);

        $this->postJson('/api/admin/ormawa', [
            'name' => 'HIMATIF',
        ])->assertStatus(403);
    }

    public function test_admin_store_returns_422_when_payload_invalid(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        // name wajib
        $this->postJson('/api/admin/ormawa', [])
            ->assertStatus(422);
    }

    public function test_admin_store_returns_201_when_payload_valid(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $payload = [
            'name'            => 'HIMATIF',
            'type_ormawa'     => 'Himpunan',
            'category_ormawa' => 'Akademik',
            'status_oprec'    => 'BUKA',
            'description'     => 'Organisasi mahasiswa bidang informatika.',
        ];

        $res = $this->postJson('/api/admin/ormawa', $payload);
        $res->assertStatus(201);

        // Pastikan tersimpan
        $this->assertDatabaseHas('ormawas', [
            'name'     => 'HIMATIF',
            'user_id'  => $admin->id,
        ]);
    }

    public function test_admin_update_returns_200_when_valid(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        // Buat data ormawa langsung via model agar stabil
        $ormawa = Ormawa::create([
            'user_id'         => $admin->id,
            'name'            => 'Ormawa Lama',
            'photo_path'      => null,
            'type_ormawa'     => 'UKM',
            'category_ormawa' => 'Non-akademik',
            'status_oprec'    => 'TUTUP',
            'description'     => 'Deskripsi lama',
        ]);

        $payloadUpdate = [
            'name'            => 'Ormawa Baru',
            'type_ormawa'     => 'UKM',
            'category_ormawa' => 'Non-akademik',
            'status_oprec'    => 'BUKA',
            'description'     => 'Deskripsi baru',
        ];

        $this->putJson('/api/admin/ormawa/' . $ormawa->id, $payloadUpdate)
            ->assertStatus(200);

        $this->assertDatabaseHas('ormawas', [
            'id'   => $ormawa->id,
            'name' => 'Ormawa Baru',
        ]);
    }

    public function test_admin_update_returns_404_when_not_found(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->putJson('/api/admin/ormawa/999999', [
            'name' => 'Test',
        ])->assertStatus(404);
    }

    public function test_admin_destroy_returns_200_when_exists(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $ormawa = Ormawa::create([
            'user_id'         => $admin->id,
            'name'            => 'Ormawa Hapus',
            'photo_path'      => null,
            'type_ormawa'     => null,
            'category_ormawa' => null,
            'status_oprec'    => null,
            'description'     => null,
        ]);

        $this->deleteJson('/api/admin/ormawa/' . $ormawa->id)
            ->assertStatus(200);

        // Jika model memakai SoftDeletes, gunakan assertSoftDeleted
        // Kalau tidak pakai soft delete, gunakan assertDatabaseMissing
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(Ormawa::class))) {
            $this->assertSoftDeleted('ormawas', ['id' => $ormawa->id]);
        } else {
            $this->assertDatabaseMissing('ormawas', ['id' => $ormawa->id]);
        }
    }

    public function test_admin_destroy_returns_404_when_not_found(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/admin/ormawa/999999')
            ->assertStatus(404);
    }
}
