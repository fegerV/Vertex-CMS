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
        Schema::create('seo_keyword_maps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название карты ключевых слов');
            $table->string('target_url')->comment('Целевой URL для ссылок');
            $table->text('keywords')->comment('JSON массив ключевых фраз и вариантов');
            $table->boolean('is_enabled')->default(true)->comment('Активность правила');
            $table->boolean('case_sensitive')->default(false)->comment('Чувствительность к регистру');
            $table->integer('max_links_per_post')->default(3)->comment('Максимум ссылок на пост');
            $table->boolean('auto_link_on_publish')->default(true)->comment('Авто-линки при публикации');
            $table->text('description')->nullable()->comment('Описание правила');
            $table->json('ai_variants')->nullable()->comment('AI-варианты ключевых фраз');
            $table->timestamps();
            
            $table->index('is_enabled');
            $table->index('auto_link_on_publish');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_keyword_maps');
    }
};
