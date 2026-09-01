<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from_url', 500)->unique();
            $table->string('to_url', 500);
            $table->integer('status_code')->default(301);
            $table->unsignedBigInteger('hits')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('metadata_json')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version', 50);
            $table->string('type', 50)->default('core');
            $table->string('status', 50)->default('enabled');
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->longText('settings_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('redirects');
    }
};

