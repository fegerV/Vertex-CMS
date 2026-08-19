<?php

namespace App\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxonomyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'entity_type' => $this->entity_type,
            'hierarchical' => (bool) $this->hierarchical,
            'settings_json' => $this->settings_json ?? [],
            'terms' => TermResource::collection($this->whenLoaded('terms'))->resolve($request),
        ];
    }
}
