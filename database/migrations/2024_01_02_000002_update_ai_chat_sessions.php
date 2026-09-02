<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Расширяет таблицу ai_chat_sessions для поддержки модульных ботов и page context
     */
    public function up(): void
    {
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            // Привязка к конкретному боту
            $table->foreignId('chatbot_id')->nullable()->after('id')->constrained('chatbots')->onDelete('set null');
            
            // Контекст страницы
            $table->string('page_uri')->nullable()->after('user_agent'); // URI текущей страницы
            $table->string('page_title')->nullable()->after('page_uri'); // Заголовок страницы
            $table->text('page_excerpt')->nullable()->after('page_title'); // Краткое описание страницы
            $table->json('page_metadata')->nullable()->after('page_excerpt'); // Доп. метаданные страницы
            
            // Информация о пользователе
            $table->foreignId('user_id')->nullable()->after('user_ip')->constrained()->onDelete('set null'); // Для авторизованных пользователей
            
            $table->index('chatbot_id');
            $table->index('page_uri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->dropForeign(['chatbot_id']);
            $table->dropForeign(['user_id']);
            $table->dropIndex(['chatbot_id']);
            $table->dropIndex(['page_uri']);
            
            $table->dropColumn([
                'chatbot_id',
                'page_uri',
                'page_title',
                'page_excerpt',
                'page_metadata',
                'user_id',
            ]);
        });
    }
};
