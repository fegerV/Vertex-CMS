<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->json('events');
            $table->string('secret', 64);
            $table->boolean('is_active')->default(true);
            $table->json('headers')->nullable();
            $table->integer('retry_count')->default(3);
            $table->integer('timeout')->default(30);
            $table->timestamps();
            
            $table->index('is_active');
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->integer('status_code')->nullable();
            $table->text('response')->nullable();
            $table->boolean('success')->default(false);
            $table->integer('attempt')->default(1);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index(['webhook_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
    }
};
