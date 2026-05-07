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
