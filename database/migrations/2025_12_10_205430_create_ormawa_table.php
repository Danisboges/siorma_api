<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ormawa', function (Blueprint $table) {

            $table->id(); // PK sesuai model

            // User pembuat ormawa
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->string('type_ormawa')->nullable();      // UKM/HMJ/Komunitas/etc
            $table->string('category_ormawa')->nullable();  // kategori
            $table->string('status_oprec')->nullable();     // open/closed
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FK
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ormawa');
    }
};
