<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Создаёт таблицу правил для движка автоматизации чат-ботов
     */
    public function up(): void
    {
        Schema::create('chatbot_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->onDelete('cascade');
            $table->string('name'); // Название правила
            $table->text('description')->nullable();
            
            // Событие (когда срабатывает правило)
            $table->string('event_type')->default('message_received'); // message_received, form_submitted, file_uploaded
            
            // Условия (массив условий в JSON)
            // Пример: [{"field": "message", "operator": "contains", "value": "цена"}, {"field": "message", "operator": "regex", "value": "/стоимость|прайс/i"}]
            $table->json('conditions')->nullable();
            
            // Действия (массив действий в JSON)
            // Пример: [{"type": "webhook", "url": "https://n8n.example.com/webhook/...", "method": "POST"}, {"type": "show_form", "form_schema": {...}}, {"type": "block_llm", "response": "..."}]
            $table->json('actions')->nullable();
            
            // Приоритет и порядок выполнения
            $table->integer('priority')->default(0); // Чем выше число, тем раньше выполняется
            $table->boolean('stop_on_match')->default(true); // Остановить обработку правил после совпадения
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['chatbot_id', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_rules');
    }
};
