<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для добавления контекста страницы в запросы чата
 * Автоматически извлекает информацию о текущей странице и добавляет её в запрос
 */
class PageContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Добавляем атрибуты page context для последующего использования
        if ($this->shouldAttachPageContext($request)) {
            $pageContext = $this->extractPageContext($request);
            
            if (!empty($pageContext)) {
                $request->merge([
                    'page_context' => $pageContext,
                ]);
                
                // Также сохраняем в attributes для доступа через middleware chain
                $request->attributes->set('page_context', $pageContext);
            }
        }

        return $next($request);
    }

    /**
     * Проверить, нужно ли добавлять контекст страницы
     */
    private function shouldAttachPageContext(Request $request): bool
    {
        // Применяем только к AI chat эндпоинтам
        return str_contains($request->path(), 'api/ai/chat') 
            || str_contains($request->path(), 'api/ai/messages');
    }

    /**
     * Извлечь контекст страницы из текущего запроса
     */
    private function extractPageContext(Request $request): array
    {
        $context = [
            'uri' => $request->headers->get('X-Page-Uri') ?? $request->input('page_uri'),
            'title' => $request->headers->get('X-Page-Title') ?? $request->input('page_title'),
            'excerpt' => $request->headers->get('X-Page-Excerpt') ?? $request->input('page_excerpt'),
            'metadata' => [],
        ];

        // Попытаться получить метаданные из JSON header
        $metadataHeader = $request->headers->get('X-Page-Metadata');
        if ($metadataHeader) {
            $decoded = json_decode($metadataHeader, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $context['metadata'] = $decoded;
            }
        }

        // Если есть page_id,可以尝试 загрузить страницу из БД
        $pageId = $request->input('page_id') ?? $request->headers->get('X-Page-Id');
        if ($pageId) {
            $pageData = $this->loadPageData($pageId);
            
            if ($pageData) {
                $context = array_merge($context, $pageData);
            }
        }

        // Фильтруем пустые значения
        return array_filter($context, fn($value) => $value !== null && $value !== '');
    }

    /**
     * Загрузить данные страницы из БД
     */
    private function loadPageData(mixed $pageId): ?array
    {
        try {
            // Используем Content\\Models\\Page если существует
            $pageClass = '\\App\\Content\\Models\\Page';
            
            if (!class_exists($pageClass)) {
                return null;
            }

            $page = $pageClass::find($pageId);
            
            if (!$page) {
                return null;
            }

            return [
                'uri' => $page->uri ?? $page->slug ?? null,
                'title' => $page->title ?? null,
                'excerpt' => $page->excerpt ?? $page->meta_description ?? null,
                'metadata' => [
                    'id' => $page->id,
                    'template' => $page->template ?? null,
                    'created_at' => $page->created_at?->toIso8601String(),
                ],
            ];

        } catch (\Exception $e) {
            \Log::warning('PageContextMiddleware: Failed to load page data', [
                'error' => $e->getMessage(),
                'page_id' => $pageId,
            ]);
            
            return null;
        }
    }
}
