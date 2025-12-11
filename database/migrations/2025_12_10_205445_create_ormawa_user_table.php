<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ormawa_user', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('ormawaID'); // sesuai model
            $table->unsignedBigInteger('user_id');

            // timestamps optional
            $table->timestamps();

            // FK
            $table->foreign('ormawaID')
                ->references('id')->on('ormawa')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ormawa_user');
    }
};
