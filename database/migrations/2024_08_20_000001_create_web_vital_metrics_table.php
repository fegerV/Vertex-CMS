<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_vital_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->string('url')->nullable();
            $table->string('metric_type'); // LCP, FID, CLS, INP, TTFB
            $table->decimal('value', 10, 3);
            $table->enum('rating', ['good', 'needs-improvement', 'poor']);
            $table->json('metadata')->nullable(); // browser, device, network info
            $table->timestamp('measured_at');
            $table->timestamps();

            $table->index(['metric_type', 'measured_at']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_vital_metrics');
    }
};
