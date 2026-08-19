<?php

namespace App\System\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'settings' => $this->settings_json ?? [],
        ];
    }
}
