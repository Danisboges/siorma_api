<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {

            $table->bigIncrements('postID'); // PK custom

            // FK
            $table->unsignedBigInteger('ormawaID')->nullable();
            $table->unsignedBigInteger('userID')->nullable();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('posterPath')->nullable();
            $table->string('status')->default('draft'); // published/draft

            $table->timestamps();

            // FK ke ormawa
            $table->foreign('ormawaID')
                ->references('id')->on('ormawa')
                ->nullOnDelete();

            // FK ke users
            $table->foreign('userID')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
