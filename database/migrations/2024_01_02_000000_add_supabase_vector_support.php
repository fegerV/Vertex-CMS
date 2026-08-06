<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для настройки Supabase pgvector и RPC функции поиска
 * 
 * Эта миграция создает:
 * 1. Расширение vector в базе данных (требуется pgvector)
 * 2. Индексы для ускорения векторного поиска
 * 3. RPC функцию search_kb_chunks для быстрого поиска похожих чанков
 * 
 * Примечание: Для работы требуется PostgreSQL с расширением pgvector
 * В Supabase это расширение установлено по умолчанию
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Создаем расширение vector если оно еще не создано
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        } catch (\Exception $e) {
            // Если расширение уже существует или недоступно, продолжаем
            \Log::info('Расширение vector уже существует или недоступно: ' . $e->getMessage());
        }

        // Добавляем колонку embedding_vector с типом vector(1536) для ai_kb_chunks
        // Если таблица уже существует, изменяем её
        if (Schema::hasTable('ai_kb_chunks')) {
            try {
                // Проверяем тип колонки и изменяем если нужно
                DB::statement('ALTER TABLE ai_kb_chunks ALTER COLUMN embedding_vector TYPE vector(1536) USING embedding_vector::vector(1536)');
            } catch (\Exception $e) {
                \Log::info('Колонка embedding_vector уже имеет правильный тип или таблица не существует: ' . $e->getMessage());
            }
        }

        // Создаем индекс для векторного поиска (IVFFlat для быстрого поиска)
        // Используем косинусное расстояние (vector_cosine_ops)
        try {
            DB::statement('
                CREATE INDEX IF NOT EXISTS ai_kb_chunks_embedding_idx 
                ON ai_kb_chunks 
                USING ivfflat (embedding_vector vector_cosine_ops)
                WITH (lists = 100)
            ');
        } catch (\Exception $e) {
            \Log::info('Не удалось создать индекс векторного поиска: ' . $e->getMessage());
        }

        // Создаем RPC функцию для поиска похожих чанков
        // Эта функция будет вызываться через Supabase REST API
        try {
            DB::statement("
                CREATE OR REPLACE FUNCTION search_kb_chunks(
                    query_embedding vector(1536),
                    match_limit INT DEFAULT 5
                )
                RETURNS TABLE(
                    id BIGINT,
                    document_id BIGINT,
                    content TEXT,
                    chunk_order INTEGER,
                    embedding_vector JSONB,
                    metadata JSONB,
                    created_at TIMESTAMPTZ,
                    updated_at TIMESTAMPTZ,
                    distance FLOAT
                )
                LANGUAGE plpgsql
                AS \$\$
                BEGIN
                    RETURN QUERY
                    SELECT
                        c.id,
                        c.document_id,
                        c.content,
                        c.chunk_order,
                        to_jsonb(c.embedding_vector) AS embedding_vector,
                        c.metadata,
                        c.created_at,
                        c.updated_at,
                        (c.embedding_vector <=> query_embedding) AS distance
                    FROM ai_kb_chunks c
                    ORDER BY c.embedding_vector <=> query_embedding
                    LIMIT match_limit;
                END;
                \$\$
            ");
        } catch (\Exception $e) {
            \Log::info('Не удалось создать RPC функцию search_kb_chunks: ' . $e->getMessage());
        }

        // Создаем индекс для ускорения обычного поиска по document_id
        if (Schema::hasTable('ai_kb_chunks')) {
            try {
                Schema::table('ai_kb_chunks', function (Blueprint $table) {
                    $table->index('document_id', 'ai_kb_chunks_document_id_idx');
                });
            } catch (\Exception $e) {
                \Log::info('Индекс document_id уже существует: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем RPC функцию
        try {
            DB::statement('DROP FUNCTION IF EXISTS search_kb_chunks(vector, INT)');
        } catch (\Exception $e) {
            \Log::info('Функция search_kb_chunks не существует: ' . $e->getMessage());
        }

        // Удаляем индексы
        try {
            DB::statement('DROP INDEX IF EXISTS ai_kb_chunks_embedding_idx');
            DB::statement('DROP INDEX IF EXISTS ai_kb_chunks_document_id_idx');
        } catch (\Exception $e) {
            \Log::info('Индексы не существуют: ' . $e->getMessage());
        }

        // Возвращаем тип колонки к text (опционально)
        try {
            DB::statement('ALTER TABLE ai_kb_chunks ALTER COLUMN embedding_vector TYPE TEXT');
        } catch (\Exception $e) {
            \Log::info('Не удалось изменить тип колонки embedding_vector: ' . $e->getMessage());
        }
    }
};
