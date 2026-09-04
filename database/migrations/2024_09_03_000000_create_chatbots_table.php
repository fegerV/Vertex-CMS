<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Конфигурация LLM
            $table->string('llm_provider')->default('openai');
            $table->string('llm_model')->default('gpt-4o-mini');
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->default(1000);
            
            // Системный промпт
            $table->text('system_prompt')->nullable();
            
            // Настройки поведения (JSON)
            $table->json('config')->nullable();
            
            // Лимиты (JSON)
            $table->json('rate_limits')->nullable();
            
            // UI настройки (JSON)
            $table->json('ui_config')->nullable();
            
            // Webhook интеграция
            $table->string('webhook_url')->nullable();
            $table->json('webhook_events')->nullable();
            
            // Привязка к страницам
            $table->json('page_ids')->nullable();
            
            // Роли пользователей
            $table->json('allowed_roles')->nullable();
            
            // Статусы
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbots');
    }
};
