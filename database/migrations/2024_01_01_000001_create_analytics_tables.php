<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('layout')->nullable();
            $table->json('widgets')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('funnel_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('step_order')->default(0);
            $table->decimal('conversion_rate', 5, 2)->nullable();
            $table->decimal('drop_off_rate', 5, 2)->nullable();
            $table->string('event_name')->nullable();
            $table->json('filter_conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('heatmaps', function (Blueprint $table) {
            $table->id();
            $table->string('page_url');
            $table->enum('heatmap_type', ['click', 'move', 'scroll']);
            $table->json('data_points')->nullable();
            $table->integer('viewport_width')->default(1920);
            $table->integer('viewport_height')->default(1080);
            $table->integer('session_count')->default(1);
            $table->timestamp('date_range_start')->nullable();
            $table->timestamp('date_range_end')->nullable();
            $table->timestamps();
            
            $table->index(['page_url', 'heatmap_type']);
            $table->index(['date_range_start', 'date_range_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heatmaps');
        Schema::dropIfExists('funnel_steps');
        Schema::dropIfExists('dashboards');
    }
};
