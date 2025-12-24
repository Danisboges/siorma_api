<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\RegistrationApiController;
use App\Http\Controllers\Api\OrmawaApiController;

// HEALTH
Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'message' => 'Siorma API running',
    ]);
});

// AUTH PUBLIC
Route::post('/login',    [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// POSTS PUBLIC (untuk user FE)
Route::get('/posts',          [PostApiController::class, 'index']);
Route::get('/posts/{postID}', [PostApiController::class, 'show']);

// ORMAWA PUBLIC (kalau memang ingin semua orang bisa lihat)
Route::get('/ormawa',      [OrmawaApiController::class, 'index']);
Route::get('/ormawa/{id}', [OrmawaApiController::class, 'show']);

// PROTECTED (BUTUH TOKEN)
Route::middleware('auth:sanctum')->group(function () {

    // ME & LOGOUT
    Route::get('/me',      [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // DASHBOARD (opsional)
    Route::get('/dashboard',       [AuthApiController::class, 'dashboardData']);
    Route::get('/admin/dashboard', [AuthApiController::class, 'adminDashboardData']);
    Route::get('/user/dashboard',  [AuthApiController::class, 'userDashboardData']);

    // ADMIN USERS
    Route::get('/admin/users',         [AuthApiController::class, 'manageUsers']);
    Route::post('/admin/users',        [AuthApiController::class, 'adminAddUser']);
    Route::get('/admin/users/{id}',    [AuthApiController::class, 'getUser']);
    Route::put('/admin/users/{id}',    [AuthApiController::class, 'updateUser']);
    Route::delete('/admin/users/{id}', [AuthApiController::class, 'deleteUser']);

    // ADMIN POSTS
    Route::get('/admin/posts',             [PostApiController::class, 'adminIndex']);
    Route::post('/admin/posts',            [PostApiController::class, 'store']);
    Route::put('/admin/posts/{postID}',    [PostApiController::class, 'update']);
    Route::delete('/admin/posts/{postID}', [PostApiController::class, 'destroy']);

    // REGISTRATIONS (user submit)
    Route::post('/posts/{postID}/registrations', [RegistrationApiController::class, 'store']);

    // ADMIN REGISTRATIONS
    Route::get('/admin/registrations',               [RegistrationApiController::class, 'adminIndex']);
    Route::patch('/admin/registrations/{id}/status', [RegistrationApiController::class, 'updateStatus']);
    Route::delete('/admin/registrations/{id}',       [RegistrationApiController::class, 'destroy']);

    Route::get('/admin/registrations/{id}/cv', [RegistrationApiController::class, 'adminViewCv']);
    Route::get('/admin/registrations/{id}/cv/download', [RegistrationApiController::class, 'adminDownloadCv']);


    // =========================
    // ADMIN ORMAWA (CRUD + LIST)
    // =========================
    // INI YANG MEMPERBAIKI ERROR 405 DI FE:
    Route::get('/admin/ormawa',          [OrmawaApiController::class, 'adminIndex']); // <-- TAMBAH INI
    Route::post('/admin/ormawa',         [OrmawaApiController::class, 'store']);
    Route::put('/admin/ormawa/{id}',     [OrmawaApiController::class, 'update']);
    Route::delete('/admin/ormawa/{id}',  [OrmawaApiController::class, 'destroy']);
});
