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
        // Таблица категорий базы знаний
        Schema::create('ai_kb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('ai_kb_categories')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Таблица документов (источников знаний)
        Schema::create('ai_kb_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('category_id')->nullable()->constrained('ai_kb_categories')->onDelete('set null');
            $table->string('file_path')->nullable(); // Путь к файлу если загружен
            $table->text('content'); // Основной текст
            $table->string('source_type')->default('manual'); // manual, file_import, url_scan
            $table->string('mime_type')->nullable();
            $table->integer('word_count')->default(0);
            $table->boolean('is_processed')->default(false); // Обработан ли на чанки
            $table->timestamps();
        });

        // Таблица чанков (фрагментов текста для векторного поиска)
        Schema::create('ai_kb_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('ai_kb_documents')->onDelete('cascade');
            $table->text('content'); // Фрагмент текста
            $table->integer('chunk_order')->default(0);
            $table->text('embedding_vector')->nullable(); // Сериализованный вектор (в реальном проекте лучше отдельное хранилище типа pgvector или Pinecone)
            $table->json('metadata')->nullable(); // Доп данные (страница, заголовок и т.д.)
            $table->timestamps();
            
            $table->index('document_id');
        });

        // Таблица истории чатов
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // ID сессии пользователя
            $table->foreignId('chatbot_id')->nullable()->constrained('chatbots')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->ipAddress('user_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('page_uri')->nullable(); // URI страницы где начат чат
            $table->string('page_title')->nullable(); // Заголовок страницы
            $table->text('page_excerpt')->nullable(); // Краткое описание страницы
            $table->json('page_metadata')->nullable(); // Дополнительные метаданные страницы
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            
            $table->index(['chatbot_id', 'is_closed']);
            $table->index('session_id');
        });

        // Таблица сообщений чата
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ai_chat_sessions')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->json('sources')->nullable(); // Какие чанки использовались для ответа (ID)
            $table->integer('tokens_used')->default(0);
            $table->float('confidence_score')->default(0.0); // Уверенность ответа
            $table->timestamps();
        });

        // Настройки RAG
        Schema::create('ai_rag_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_rag_settings');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('ai_kb_chunks');
        Schema::dropIfExists('ai_kb_documents');
        Schema::dropIfExists('ai_kb_categories');
    }
};
