<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_public_posts_index_returns_200(): void
    {
        $this->getJson('/api/posts')
            ->assertStatus(200);
    }

    public function test_public_posts_show_returns_404_for_nonexistent_id(): void
    {
        $this->getJson('/api/posts/999999')
            ->assertStatus(404);
    }

    public function test_admin_posts_index_requires_auth(): void
    {
        $this->getJson('/api/admin/posts')
            ->assertStatus(401);
    }

    public function test_admin_can_access_admin_posts_index_when_authenticated(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/posts')
            ->assertStatus(200);
    }

    public function test_admin_store_returns_422_when_payload_empty(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/posts', [])
            ->assertStatus(422);
    }
}
