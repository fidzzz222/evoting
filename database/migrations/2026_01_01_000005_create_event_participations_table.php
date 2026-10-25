<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('voters')->cascadeOnDelete();
            $table->boolean('has_voted')->default(false);
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();

            // Constraint unik untuk mencegah pencoblosan ganda per event
            $table->unique(['event_id', 'voter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_participations');
    }
};
