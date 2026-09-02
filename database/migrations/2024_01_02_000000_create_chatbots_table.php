<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Создаёт таблицу chatbots для модульной архитектуры AI-ассистентов
     */
    public function up(): void
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название бота
            $table->string('slug')->unique(); // Уникальный идентификатор для фронтенда
            $table->text('description')->nullable(); // Описание назначения
            
            // Конфигурация LLM
            $table->string('provider')->default('openai'); // openai, anthropic, ollama
            $table->string('model')->default('gpt-3.5-turbo'); // Модель LLM
            $table->json('provider_config')->nullable(); // Доп. настройки провайдера
            
            // Системные инструкции
            $table->text('system_prompt')->nullable(); // Базовый системный промпт
            $table->boolean('use_page_context')->default(true); // Использовать контекст страницы
            $table->boolean('use_knowledge_base')->default(true); // Использовать базу знаний
            
            // Источники знаний
            $table->json('knowledge_bases')->nullable(); // IDs подключенных баз знаний
            $table->integer('max_context_chunks')->default(5); // Макс. чанков для контекста
            
            // Лимиты и ограничения
            $table->integer('max_tokens_per_message')->default(500); // Макс. токенов в ответе
            $table->integer('rate_limit_per_minute')->default(10); // Лимит сообщений в минуту
            $table->integer('rate_limit_per_hour')->default(100); // Лимит сообщений в час
            $table->integer('max_session_messages')->default(50); // Макс. сообщений в сессии
            
            // Настройки UI
            $table->json('ui_config')->nullable(); // Цвет, аватар, приветственное сообщение
            $table->boolean('is_active')->default(true); // Статус бота
            
            // Привязка к страницам/разделам
            $table->json('page_restrictions')->nullable(); // Ограничения по страницам (URI patterns)
            $table->json('role_restrictions')->nullable(); // Ограничения по ролям пользователей
            
            // Capabilities toggles
            $table->boolean('enable_web_search')->default(false); // Веб-поиск
            $table->boolean('enable_image_generation')->default(false); // Генерация изображений
            $table->boolean('enable_voice_input')->default(false); // Голосовой ввод
            $table->boolean('enable_file_upload')->default(false); // Загрузка файлов
            $table->integer('max_file_size_mb')->default(20); // Макс. размер файла
            
            // Интеграции
            $table->string('webhook_url')->nullable(); // URL для n8n webhook
            $table->json('webhook_triggers')->nullable(); // Триггеры для вызова webhook
            
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
