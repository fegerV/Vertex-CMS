<?php

namespace App\System\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicSiteSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'site' => $this->resource['site'] ?? [],
            'seo' => $this->resource['seo'] ?? [],
            'api' => $this->resource['api'] ?? [],
            'pwa' => $this->resource['pwa'] ?? [],
        ];
    }
}
