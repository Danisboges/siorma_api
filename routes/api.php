<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\RegistrationApiController;

// Health check
Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'message' => 'Siorma API running',
    ]);
});

// ========= PUBLIC AUTH =========
Route::post('/login',    [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// ========= PUBLIC POST (untuk FE user) =========
Route::get('/posts',          [PostApiController::class, 'index']);
Route::get('/posts/{postID}', [PostApiController::class, 'show']);

// ========= PROTECTED (BUTUH TOKEN) =========
Route::middleware('auth:sanctum')->group(function () {

    // Profil dan logout
    Route::get('/me',      [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Dashboard data
    Route::get('/dashboard',        [AuthApiController::class, 'dashboardData']);
    Route::get('/admin/dashboard',  [AuthApiController::class, 'adminDashboardData']);
    Route::get('/user/dashboard',   [AuthApiController::class, 'userDashboardData']);

    // ===== ADMIN – USER MANAGEMENT =====
    Route::get('/admin/users',         [AuthApiController::class, 'manageUsers']);
    Route::post('/admin/users',        [AuthApiController::class, 'adminAddUser']);
    Route::get('/admin/users/{id}',    [AuthApiController::class, 'getUser']);
    Route::put('/admin/users/{id}',    [AuthApiController::class, 'updateUser']);
    Route::delete('/admin/users/{id}', [AuthApiController::class, 'deleteUser']);

    // ===== ADMIN – POSTS MANAGEMENT =====
    Route::get('/admin/posts',              [PostApiController::class, 'adminIndex']);
    Route::post('/admin/posts',             [PostApiController::class, 'store']);
    Route::put('/admin/posts/{postID}',     [PostApiController::class, 'update']);
    Route::delete('/admin/posts/{postID}',  [PostApiController::class, 'destroy']);

    // ===== REGISTRATION EVENT =====
    Route::post('/posts/{postID}/registrations', [RegistrationApiController::class, 'store']);

    Route::get('/admin/registrations',                 [RegistrationApiController::class, 'adminIndex']);
    Route::patch('/admin/registrations/{id}/status',   [RegistrationApiController::class, 'updateStatus']);
    Route::delete('/admin/registrations/{id}',         [RegistrationApiController::class, 'destroy']);
});
