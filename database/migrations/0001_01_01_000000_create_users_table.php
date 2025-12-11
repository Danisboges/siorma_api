<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('name');
    $table->string('username')->nullable();
    $table->string('email')->unique();
    $table->string('password');

    $table->string('role')->default('user');

    // cukup kolomnya saja, TANPA foreign() dulu
    $table->unsignedBigInteger('ormawaID')->nullable();

    $table->rememberToken();
    $table->timestamps();

    // JANGAN ada baris seperti ini di file ini:
    // $table->foreign('ormawaID')->references('id')->on('ormawa')->nullOnDelete();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
