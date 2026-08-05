<?php

namespace App\Services\AI;

use Laravel\Scout\EngineManager;
use Illuminate\Database\Eloquent\Builder;

class SmartSearchService
{
    public function search(string $query, array $options = []): array
    {
        $models = $options['models'] ?? [
            \App\Models\Ecommerce\Product::class,
            \App\Models\Content\Page::class,
            \App\Models\Content\Post::class,
        ];

        $results = [];

        foreach ($models as $model) {
            $modelResults = $this->searchInModel($model, $query, $options);
            if (!empty($modelResults)) {
                $results[$model] = $modelResults;
            }
        }

        return $results;
    }

    private function searchInModel(string $model, string $query, array $options = [])
    {
        // Если модель использует Scout
        if (in_array(\Laravel\Scout\Searchable::class, class_uses_recursive($model))) {
            return $model::search($query)->take($options['limit'] ?? 10)->get();
        }

        // Fallback на полнотекстовый поиск в БД
        $queryBuilder = $model::query();

        // Получаем searchable колонки
        $searchableColumns = $this->getSearchableColumns($model);

        if (empty($searchableColumns)) {
            return [];
        }

        // Строим запрос с поиском по нескольким колонкам
        $queryBuilder->where(function ($q) use ($searchableColumns, $query) {
            foreach ($searchableColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $q->{$method}($column, 'LIKE', "%{$query}%");
            }
        });

        return $queryBuilder->take($options['limit'] ?? 10)->get();
    }

    private function getSearchableColumns(string $model): array
    {
        // Определяем колонки для поиска на основе модели
        return match($model) {
            \App\Models\Ecommerce\Product::class => ['name', 'description', 'sku'],
            \App\Models\Content\Page::class => ['title', 'slug', 'meta_description'],
            \App\Models\Content\Post::class => ['title', 'slug', 'excerpt', 'content'],
            default => ['name', 'title', 'description'],
        };
    }

    public function semanticSearch(string $query, string $model, int $limit = 10): array
    {
        // Используем AI для расширения запроса синонимами
        $generationService = new ContentGenerationService();
        
        $prompt = "Подбери 5 синонимов и связанных слов для поискового запроса: \"{$query}\". Верни только слова через запятую.";
        
        $result = $generationService->generateText($prompt, [
            'max_tokens' => 100,
            'temperature' => 0.5,
        ]);

        $expandedQuery = $query;
        if ($result['success']) {
            $synonyms = explode(',', $result['content']);
            $expandedQuery = $query . ' ' . implode(' ', array_map('trim', $synonyms));
        }

        // Ищем по расширенному запросу
        return $this->searchInModel($model, $expandedQuery, ['limit' => $limit])
            ->toArray();
    }

    public function searchWithFilters(string $query, array $filters, string $model): array
    {
        $results = $this->searchInModel($model, $query, ['limit' => 100]);

        // Применяем фильтры
        $collection = collect($results);

        if (isset($filters['price_min'])) {
            $collection = $collection->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $collection = $collection->where('price', '<=', $filters['price_max']);
        }

        if (isset($filters['category_id'])) {
            $collection = $collection->where('category_id', $filters['category_id']);
        }

        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $collection = $collection->where('stock_quantity', '>', 0);
        }

        if (isset($filters['brand'])) {
            $collection = $collection->where('brand', $filters['brand']);
        }

        // Сортировка
        if (isset($filters['sort'])) {
            $collection = match($filters['sort']) {
                'price_asc' => $collection->sortBy('price'),
                'price_desc' => $collection->sortByDesc('price'),
                'newest' => $collection->sortByDesc('created_at'),
                'popular' => $collection->sortByDesc('views_count'),
                default => $collection,
            };
        }

        return $collection->take($filters['limit'] ?? 20)->values()->toArray();
    }

    public function suggestQueries(string $partialQuery): array
    {
        // Используем AI для генерации подсказок
        $generationService = new ContentGenerationService();
        
        $prompt = "Пользователь начал вводить поисковый запрос: \"{$partialQuery}\". Предложи 5 вариантов завершения запроса для интернет-магазина. Верни только варианты, каждый с новой строки.";
        
        $result = $generationService->generateText($prompt, [
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);

        if ($result['success']) {
            $suggestions = explode("\n", trim($result['content']));
            return array_filter(array_map('trim', $suggestions));
        }

        // Fallback на популярные запросы
        return $this->getPopularQueries($partialQuery);
    }

    private function getPopularQueries(string $prefix): array
    {
        // Здесь должна быть логика получения популярных запросов из логов
        $popularQueries = [
            'смартфон',
            'ноутбук',
            'наушники',
            'планшет',
            'часы',
        ];

        return collect($popularQueries)
            ->filter(fn($q) => stripos($q, $prefix) === 0)
            ->take(5)
            ->toArray();
    }

    public function indexForSearch($model): void
    {
        // Индексация модели для поиска
        if (method_exists($model, 'searchable')) {
            $model->searchable();
        }
    }

    public function removeFromSearch($model): void
    {
        // Удаление модели из поискового индекса
        if (method_exists($model, 'unsearchable')) {
            $model->unsearchable();
        }
    }
}
