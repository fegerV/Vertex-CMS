<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_analytics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->date('date'); // aggregate per day
            $table->integer('views')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('submissions')->default(0);
            $table->integer('avg_time_seconds')->default(0);
            $table->json('top_fields')->nullable(); // most interacted fields
            $table->timestamps();

            $table->unique(['form_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_analytics');
    }
};
