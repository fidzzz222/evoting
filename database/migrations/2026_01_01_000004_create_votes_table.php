<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->timestamp('voted_at');

            // Index performa agregasi penghitungan suara cepat
            $table->index(['event_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
