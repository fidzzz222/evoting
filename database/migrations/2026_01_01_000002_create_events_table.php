<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->default('KETUA_OSIS'); // KETUA_OSIS, KETUA_MPK, KETUA_KELAS, ORGANISASI, VOTING_KEGIATAN
            $table->string('target_scope', 20)->default('ALL');
            $table->string('target_class', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('room_code')->unique();
            $table->string('invite_token', 64)->nullable()->unique();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'CLOSED'])->default('DRAFT');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
