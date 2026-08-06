<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица для тегов медиа
        Schema::create('media_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Связь медиа с тегами (многие ко многим)
        Schema::create('media_taggable', function (Blueprint $table): void {
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('media_tag_id')->constrained('media_tags')->cascadeOnDelete();
            $table->primary(['media_id', 'media_tag_id']);
        });

        // Таблица версий файлов
        Schema::create('media_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('disk', 100)->default('public');
            $table->string('filename');
            $table->string('original_filename');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('path', 500);
            $table->string('url', 500);
            $table->text('changes_description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Таблица для отслеживания использования медиа (где используется)
        Schema::create('media_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('usable_type'); // Модель где используется (Page, Product, etc.)
            $table->unsignedBigInteger('usable_id');
            $table->string('field_name')->default('content'); // Поле где используется
            $table->timestamps();

            $table->unique(['media_id', 'usable_type', 'usable_id', 'field_name']);
        });

        // Добавляем поля в таблицу media для улучшенной работы
        Schema::table('media', function (Blueprint $table): void {
            $table->json('tags_json')->nullable()->after('metadata_json');
            $table->boolean('is_optimized')->default(false)->after('metadata_json');
            $table->json('exif_data_json')->nullable()->after('is_optimized');
            $table->json('ai_data_json')->nullable()->after('exif_data_json');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn(['tags_json', 'is_optimized', 'exif_data_json', 'ai_data_json']);
        });

        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media_versions');
        Schema::dropIfExists('media_taggable');
        Schema::dropIfExists('media_tags');
    }
};
