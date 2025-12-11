<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('post_id'); // FK → posts.postID

            $table->string('full_name');
            $table->string('nim');
            $table->string('email');
            $table->string('phone');
            $table->string('organization')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');

            $table->timestamps();

            // FK
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('post_id')
                ->references('postID')->on('posts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
