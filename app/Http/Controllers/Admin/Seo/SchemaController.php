<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SchemaController extends Controller
{
    /**
     * Отображение конструктора Schema.org
     */
    public function index()
    {
        $pages = Page::select('id', 'title', 'slug')->latest()->take(100)->get();
        
        return view('admin.seo.schema-builder.index', compact('pages'));
    }

    /**
     * Генерация JSON-LD разметки
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Organization,LocalBusiness,Product,Article,FAQPage,BreadcrumbList,WebSite,Person,Event,Recipe',
            'data' => 'required|array',
        ]);

        $type = $request->type;
        $data = $request->data;

        $schema = $this->buildSchema($type, $data);

        return response()->json([
            'success' => true,
            'schema' => $schema,
            'preview' => json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Сохранение разметки на страницу
     */
    public function saveToPage(Request $request)
    {
        $request->validate([
            'page_id' => 'required|exists:pages,id',
            'schema_json' => 'required|json',
            'schema_type' => 'required|string',
        ]);

        $page = Page::findOrFail($request->page_id);
        
        // Получаем текущие SEO данные или создаем новые
        $seoData = $page->seo_data ?? [];
        $seoData['schema_markup'] = $request->schema_json;
        $seoData['schema_type'] = $request->schema_type;
        
        $page->update([
            'seo_data' => $seoData,
        ]);

        return redirect()->back()->with('success', 'Schema.org разметка сохранена!');
    }

    /**
     * Построение схемы в зависимости от типа
     */
    private function buildSchema($type, $data)
    {
        $baseSchema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
        ];

        switch ($type) {
            case 'Organization':
                $schema = array_merge($baseSchema, [
                    'name' => $data['name'] ?? '',
                    'url' => $data['url'] ?? config('app.url'),
                    'logo' => $data['logo'] ?? null,
                    'contactPoint' => isset($data['phone']) ? [
                        '@type' => 'ContactPoint',
                        'telephone' => $data['phone'],
                        'contactType' => $data['contact_type'] ?? 'customer service',
                    ] : null,
                    'sameAs' => $this->parseSocialLinks($data),
                ]);
                break;

            case 'LocalBusiness':
                $schema = array_merge($baseSchema, [
                    'name' => $data['name'] ?? '',
                    'image' => $data['image'] ?? null,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => $data['street_address'] ?? '',
                        'addressLocality' => $data['city'] ?? '',
                        'postalCode' => $data['postal_code'] ?? '',
                        'addressCountry' => $data['country'] ?? '',
                    ],
                    'geo' => isset($data['latitude']) && isset($data['longitude']) ? [
                        '@type' => 'GeoCoordinates',
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                    ] : null,
                    'telephone' => $data['phone'] ?? null,
                    'openingHours' => $data['opening_hours'] ?? null,
                    'priceRange' => $data['price_range'] ?? null,
                ]);
                break;

            case 'Product':
                $schema = array_merge($baseSchema, [
                    'name' => $data['name'] ?? '',
                    'description' => $data['description'] ?? '',
                    'image' => $this->ensureArray($data['image'] ?? []),
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $data['price'] ?? 0,
                        'priceCurrency' => $data['currency'] ?? 'RUB',
                        'availability' => $data['availability'] ?? 'https://schema.org/InStock',
                        'url' => $data['url'] ?? config('app.url'),
                    ],
                    'brand' => isset($data['brand']) ? [
                        '@type' => 'Brand',
                        'name' => $data['brand'],
                    ] : null,
                    'aggregateRating' => isset($data['rating']) ? [
                        '@type' => 'AggregateRating',
                        'ratingValue' => $data['rating'],
                        'reviewCount' => $data['review_count'] ?? 0,
                    ] : null,
                ]);
                break;

            case 'Article':
                $schema = array_merge($baseSchema, [
                    'headline' => $data['headline'] ?? '',
                    'image' => $this->ensureArray($data['image'] ?? []),
                    'datePublished' => $data['date_published'] ?? now()->toIso8601String(),
                    'dateModified' => $data['date_modified'] ?? now()->toIso8601String(),
                    'author' => isset($data['author']) ? [
                        '@type' => 'Person',
                        'name' => $data['author'],
                    ] : null,
                    'publisher' => isset($data['publisher']) ? [
                        '@type' => 'Organization',
                        'name' => $data['publisher'],
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $data['publisher_logo'] ?? null,
                        ],
                    ] : null,
                ]);
                break;

            case 'FAQPage':
                $mainEntity = [];
                if (isset($data['questions']) && is_array($data['questions'])) {
                    foreach ($data['questions'] as $qa) {
                        $mainEntity[] = [
                            '@type' => 'Question',
                            'name' => $qa['question'] ?? '',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => $qa['answer'] ?? '',
                            ],
                        ];
                    }
                }
                $schema = array_merge($baseSchema, [
                    'mainEntity' => $mainEntity,
                ]);
                break;

            case 'BreadcrumbList':
                $itemListElement = [];
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $index => $item) {
                        $itemListElement[] = [
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'name' => $item['name'] ?? '',
                            'item' => $item['url'] ?? config('app.url'),
                        ];
                    }
                }
                $schema = array_merge($baseSchema, [
                    'itemListElement' => $itemListElement,
                ]);
                break;

            case 'WebSite':
                $schema = array_merge($baseSchema, [
                    'name' => $data['name'] ?? config('app.name'),
                    'url' => $data['url'] ?? config('app.url'),
                    'potentialAction' => isset($data['search']) && $data['search'] ? [
                        '@type' => 'SearchAction',
                        'target' => $data['search_url'] ?? config('app.url') . '/search?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ] : null,
                ]);
                break;

            case 'Person':
                $schema = array_merge($baseSchema, [
                    'name' => $data['name'] ?? '',
                    'jobTitle' => $data['job_title'] ?? null,
                    'worksFor' => isset($data['company']) ? [
                        '@type' => 'Organization',
                        'name' => $data['company'],
                    ] : null,
                    'url' => $data['url'] ?? null,
                    'sameAs' => $this->parseSocialLinks($data),
                ]);
                break;

            default:
                $schema = $baseSchema;
        }

        // Удаляем null значения
        return $this->filterNulls($schema);
    }

    /**
     * Парсинг социальных ссылок
     */
    private function parseSocialLinks($data)
    {
        $links = [];
        $socialFields = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'vk'];
        
        foreach ($socialFields as $social) {
            if (!empty($data[$social])) {
                $links[] = $data[$social];
            }
        }
        
        return !empty($links) ? $links : null;
    }

    /**
     * Преобразование в массив если нужно
     */
    private function ensureArray($value)
    {
        if (is_string($value) && !empty($value)) {
            return [$value];
        }
        return $value;
    }

    /**
     * Фильтрация null значений
     */
    private function filterNulls($array)
    {
        foreach ($array as $key => $value) {
            if ($value === null) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $array[$key] = $this->filterNulls($value);
            }
        }
        return $array;
    }
}
