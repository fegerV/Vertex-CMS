<?php

namespace App\Seo\Services;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;

class SeoMetaService
{
    public const ROBOTS = [
        'index, follow',
        'noindex, follow',
        'index, nofollow',
        'noindex, nofollow',
    ];

    public function updateFor(Model $entity, array $payload): SeoMeta
    {
        return SeoMeta::query()->updateOrCreate(
            [
                'entity_type' => $entity::class,
                'entity_id' => $entity->getKey(),
            ],
            [
                'title' => $payload['seo_title'] ?? null,
                'description' => $payload['seo_description'] ?? null,
                'canonical_url' => $payload['seo_canonical_url'] ?? null,
                'robots' => $payload['seo_robots'] ?? 'index, follow',
                'og_title' => $payload['seo_og_title'] ?? null,
                'og_description' => $payload['seo_og_description'] ?? null,
                'og_image' => $payload['seo_og_image'] ?? null,
                'schema_json' => $this->decodeJson($payload['seo_schema_json'] ?? null),
                'include_in_sitemap' => (bool) ($payload['seo_include_in_sitemap'] ?? false),
            ],
        );
    }

    private function decodeJson(?string $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}

