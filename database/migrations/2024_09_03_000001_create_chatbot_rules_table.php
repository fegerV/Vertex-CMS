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
        Schema::create('chatbot_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            
            // Тип события (message_received, form_submitted и т.д.)
            $table->string('event_type')->default('message_received');
            
            // Условия (JSON массив)
            // Пример: [{"field": "message", "operator": "contains", "value": "цена"}]
            $table->json('conditions')->nullable();
            
            // Действия (JSON массив)
            // Пример: [{"type": "webhook", "url": "..."}, {"type": "show_form", "form_schema": {...}}]
            $table->json('actions')->nullable();
            
            // Приоритет (чем выше число, тем выше приоритет)
            $table->integer('priority')->default(0);
            
            // Остановить обработку после совпадения
            $table->boolean('stop_on_match')->default(false);
            
            // Статус
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
