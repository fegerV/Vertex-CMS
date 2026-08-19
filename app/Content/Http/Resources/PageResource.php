<?php

namespace App\Content\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'uri' => $this->uri,
            'status' => $this->status,
            'template' => $this->template,
            'content_json' => $this->content_json,
            'custom_fields_json' => $this->custom_fields_json ?? [],
            'custom_fields' => collect($this->custom_fields_json ?? [])
                ->mapWithKeys(fn (array $field) => [$field['key'] => $field['value'] ?? null])
                ->all(),
            'terms' => $this->whenLoaded('terms', fn () => $this->terms->map(fn ($term) => [
                'id' => $term->id,
                'name' => $term->name,
                'slug' => $term->slug,
                'taxonomy' => $term->taxonomy?->slug,
                'archive_url' => url("/taxonomy/{$term->taxonomy?->slug}/{$term->slug}"),
            ])->values()->all()),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => $this->when($this->seoMeta, [
                'title' => $this->seoMeta->title,
                'description' => $this->seoMeta->description,
                'canonical_url' => $this->seoMeta->canonical_url,
                'robots' => $this->seoMeta->robots,
                'og_title' => $this->seoMeta->og_title,
                'og_description' => $this->seoMeta->og_description,
                'og_image_url' => $this->seoMeta->ogImage?->url ?? null,
                'og_image_id' => $this->seoMeta->og_image,
                'include_in_sitemap' => $this->seoMeta->include_in_sitemap,
                'schema_json' => $this->seoMeta->schema_json,
            ]),
            'parent_uri' => $this->parent?->uri,
        ];
    }
}
