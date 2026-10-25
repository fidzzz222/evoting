<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique(); // NISN / NIP
            $table->string('nama');
            $table->string('kelas')->nullable(); // Misal: XII MIPA 1, Dewan Guru
            $table->enum('type', ['SISWA', 'GURU'])->default('SISWA');
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};
