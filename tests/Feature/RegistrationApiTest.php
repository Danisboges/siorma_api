<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_registration_requires_authentication(): void
    {
        $this->postJson('/api/posts/1/registrations', [])
            ->assertStatus(401);
    }

    public function test_admin_can_access_registrations_index(): void
    {
        $admin = User::where('role', 'admin')->first();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/registrations')
            ->assertStatus(200);
    }
}
