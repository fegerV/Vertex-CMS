<?php

namespace App\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'taxonomy_id' => $this->taxonomy_id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'seo_json' => $this->seo_json ?? [],
            'archive_url' => url("/taxonomy/{$this->taxonomy?->slug}/{$this->slug}"),
        ];
    }
}
