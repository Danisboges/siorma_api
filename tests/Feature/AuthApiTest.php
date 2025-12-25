<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);

        // bersihkan rate limiter
        RateLimiter::clear('login|m.daniswara.m@gmail.com|127.0.0.1');
    }

    public function test_health_endpoint_ok(): void
    {
        $this->getJson('/api/health')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }

    public function test_login_success_admin(): void
    {
        $this->postJson('/api/login', [
            'email'    => 'm.daniswara.m@gmail.com',
            'password' => '123456',
        ])->assertStatus(200)
          ->assertJsonPath('message', 'Login berhasil')
          ->assertJsonStructure(['success','message','data' => ['token','user']]);
    }

    public function test_login_fails_wrong_password_returns_401(): void
    {
        $this->postJson('/api/login', [
            'email'    => 'm.daniswara.m@gmail.com',
            'password' => 'salah',
        ])->assertStatus(401);
    }

    public function test_login_validation_fails_returns_422(): void
    {
        $this->postJson('/api/login', [
            'email' => 'invalid-email',
        ])->assertStatus(422);
    }

    public function test_login_rate_limit_returns_429_after_too_many_attempts(): void
    {
        for ($i=0; $i<6; $i++) {
            $res = $this->postJson('/api/login', [
                'email'    => 'm.daniswara.m@gmail.com',
                'password' => 'salah',
            ]);
        }

        $res->assertStatus(429)
            ->assertJsonStructure(['success','message','errors' => ['retry_after']]);
    }

    public function test_register_success_returns_201(): void
    {
        $u = 'user_' . Str::random(6);

        $this->postJson('/api/register', [
            'name'                  => 'User Baru',
            'username'              => $u,
            'email'                 => $u.'@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ])->assertStatus(201)
          ->assertJsonPath('message', 'Akun berhasil dibuat')
          ->assertJsonStructure(['success','message','data' => ['token','user']]);
    }

    public function test_register_validation_fails_returns_422(): void
    {
        $this->postJson('/api/register', [])
            ->assertStatus(422);
    }

    public function test_register_duplicate_email_returns_422(): void
    {
        $this->postJson('/api/register', [
            'name'                  => 'Dup',
            'username'              => 'dup_'.Str::random(4),
            'email'                 => 'm.daniswara.m@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ])->assertStatus(422);
    }

    public function test_register_password_confirmation_mismatch_returns_422(): void
    {
        $u = 'user_' . Str::random(6);

        $this->postJson('/api/register', [
            'name'                  => 'Mismatch',
            'username'              => $u,
            'email'                 => $u.'@gmail.com',
            'password'              => '123456',
            'password_confirmation' => 'beda123',
        ])->assertStatus(422);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_200_when_authenticated(): void
    {
        $user = User::where('email', 'kato@gmail.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertStatus(200);
    }

    public function test_logout_returns_200_when_authenticated(): void
    {
        $user = User::where('email', 'kato@gmail.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->postJson('/api/logout')
            ->assertStatus(200);
    }

    public function test_admin_dashboard_returns_403_for_non_admin(): void
    {
        $user = User::where('email', 'kato@gmail.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_admin_dashboard_returns_200_for_admin(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_manage_users_requires_admin(): void
    {
        $user = User::where('email', 'kato@gmail.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/users')->assertStatus(403);
    }

    public function test_manage_users_returns_200_for_admin(): void
    {
        $admin = User::where('email', 'm.daniswara.m@gmail.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/users')
            ->assertStatus(200)
            ->assertJsonStructure(['success','message','data' => ['roles','users']]);
    }
}
