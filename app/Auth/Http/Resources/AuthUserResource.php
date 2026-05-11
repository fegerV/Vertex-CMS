<?php

namespace App\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = $this->roles ?? collect();
        $permissions = $roles
            ->flatMap(fn ($role) => $role->permissions ?? collect())
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'avatar' => $this->avatar,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'roles' => $roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ])->values()->all(),
            'permissions' => $permissions->all(),
        ];
    }
}
