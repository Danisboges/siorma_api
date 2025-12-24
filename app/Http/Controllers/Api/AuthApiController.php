<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends BaseApiController
{
    /**
     * POST /api/login
     * Login dengan rate limit + token Sanctum
     */
    public function login(Request $request)
    {
        // 1) VALIDASI INPUT
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            // HTTP 422
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $email = $request->email;
        $ip    = $request->ip();
        $key   = 'login|' . $email . '|' . $ip;

        // 2) CEK RATE LIMIT
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            // HTTP 429
            return $this->error(
                'Terlalu banyak percobaan login. Coba lagi dalam ' . $seconds . ' detik.',
                429,
                ['retry_after' => $seconds]
            );
        }

        // 3) CEK CREDENTIAL
        // Pastikan guard-nya benar. Umumnya pakai guard 'web' untuk Sanctum
        if (! Auth::guard('web')->attempt($request->only('email', 'password'))) {
            // salah → tambah hit, blokir 60 detik
            RateLimiter::hit($key, 60);

            // HTTP 401
            return $this->error('Email atau password salah.', 401);
        }

        // 4) BERHASIL LOGIN
        RateLimiter::clear($key);

        /** @var \App\Models\User $user */
        $user  = Auth::guard('web')->user();  // atau $request->user() kalau guard default = web
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => $user,
        ], 'Login berhasil');
    }

    /**
     * POST /api/register
     * Register user baru (role default: user)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'username'              => 'required|string|max:15',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => $user,
        ], 'Akun berhasil dibuat', 201);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        return $this->success($request->user(), 'User saat ini');
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        // Hapus token yang dipakai sekarang
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Anda telah logout.');
    }

    /**
     * GET /api/dashboard
     * Versi JSON dari dashboard() (umum)
     */
    public function dashboardData(Request $request)
    {
        // Silakan isi sesuai kebutuhan, untuk contoh sederhana:
        return $this->success([
            'role'  => $request->user()->role,
            'name'  => $request->user()->name,
            'email' => $request->user()->email,
        ], 'Data dashboard umum');
    }

    /**
     * GET /api/admin/dashboard
     * Versi JSON dari adminDashboard()
     */
    public function adminDashboardData(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $posts = Post::with(['user', 'ormawa'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return $this->success([
            'posts' => $posts,
        ], 'Data dashboard admin');
    }

    /**
     * GET /api/user/dashboard
     * Versi JSON dari userDashboard()
     */
    public function userDashboardData(Request $request)
    {
        if (! in_array($request->user()->role, ['admin', 'user', 'bendahara', 'sales'])) {
            return $this->error('Role tidak dikenali.', 403);
        }

        $posts = Post::with(['user', 'ormawa'])
            ->where('status', 'published')
            ->latest('created_at')
            ->take(10)
            ->get();

        return $this->success([
            'posts' => $posts,
        ], 'Data dashboard user');
    }

    // ==========================
    // ADMIN – USER MANAGEMENT
    // ==========================

    /**
     * GET /api/admin/users
     * Versi JSON dari manageUsers()
     */
    public function manageUsers(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $roles = ['admin', 'user', 'bendahara', 'sales'];
        $users = User::latest()->get();

        return $this->success([
            'roles' => $roles,
            'users' => $users,
        ], 'Daftar user');
    }

    /**
     * POST /api/admin/users
     * Versi JSON dari adminAddUser()
     */
    public function adminAddUser(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6|confirmed',
            'role'                  => 'required|in:admin,user,bendahara,sales',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return $this->success($user, 'User berhasil ditambahkan!', 201);
    }

    /**
     * GET /api/admin/users/{id}
     * Versi JSON dari editUser() (ambil detail user)
     */
    public function getUser(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $user  = User::findOrFail($id);
        $roles = ['admin', 'user', 'bendahara', 'sales'];

        return $this->success([
            'user'  => $user,
            'roles' => $roles,
        ], 'Detail user');
    }

    /**
     * PUT /api/admin/users/{id}
     * Versi JSON dari updateUser()
     */
    public function updateUser(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'role'  => 'required|in:admin,user,bendahara,sales',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $user = User::findOrFail($id);
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        return $this->success($user, 'User berhasil diperbarui!');
    }

    /**
     * DELETE /api/admin/users/{id}
     * Versi JSON dari deleteUser()
     */
    public function deleteUser(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return $this->success(null, 'User berhasil dihapus!');
    }
}
