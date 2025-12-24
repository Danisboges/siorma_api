<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // pastikan tipe sama: unsignedBigInteger dan nullable (sesuai screenshot)
            $table->unsignedBigInteger('ormawaID')->nullable()->change();

            // tambahkan FK
            $table->foreign('ormawaID')
                ->references('id')
                ->on('ormawa')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ormawaID']);
        });
    }
};
