<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_registration_store_requires_authentication(): void
    {
        $this->postJson('/api/posts/1/registrations', [])
            ->assertStatus(401);
    }

    public function test_admin_registrations_index_requires_authentication(): void
    {
        $this->getJson('/api/admin/registrations')
            ->assertStatus(401);
    }

    public function test_admin_can_access_registrations_index(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/registrations')
            ->assertStatus(200);
    }

    public function test_admin_view_cv_requires_authentication(): void
    {
        $this->getJson('/api/admin/registrations/1/cv')->assertStatus(401);
    }

    public function test_admin_download_cv_requires_authentication(): void
    {
        $this->getJson('/api/admin/registrations/1/cv/download')->assertStatus(401);
    }

    public function test_admin_view_cv_returns_404_when_registration_not_found(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        // id yang tidak ada -> umumnya 404
        $this->getJson('/api/admin/registrations/999999/cv')
            ->assertStatus(404);
    }

    public function test_admin_download_cv_returns_404_when_registration_not_found(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/registrations/999999/cv/download')
            ->assertStatus(404);
    }
}
