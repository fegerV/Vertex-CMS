<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_aggregates', function (Blueprint $table): void {
            $table->id();
            $table->string('aggregate_key', 64)->unique();
            $table->date('visit_date')->index();
            $table->string('kind', 50)->index();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('path', 500);
            $table->string('title')->nullable();
            $table->unsignedInteger('visits')->default(0);
            $table->unsignedInteger('visitors')->default(0);
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('analytics_visitors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('aggregate_id')->constrained('analytics_aggregates')->cascadeOnDelete();
            $table->string('visitor_hash', 64);
            $table->timestamp('created_at')->nullable();

            $table->unique(['aggregate_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_visitors');
        Schema::dropIfExists('analytics_aggregates');
    }
};
