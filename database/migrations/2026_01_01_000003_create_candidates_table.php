<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->integer('candidate_number');
            $table->string('name');
            $table->string('photo_url')->nullable();
            $table->text('description')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
