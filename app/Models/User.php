<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('slug', $permission))
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->whereIn('slug', $permissions))
            ->exists();
    }

    public function apiAbilities(): array
    {
        $roles = $this->roles()->with('permissions')->get();
        $permissionSlugs = $roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
            ->filter()
            ->unique()
            ->values();

        if ($roles->contains(fn (Role $role) => $role->slug === 'super-admin')) {
            return ['*'];
        }

        $scopes = collect();

        if ($permissionSlugs->contains(fn (string $slug) => str_starts_with($slug, 'pages.'))) {
            $scopes->push('content:read');
        }

        if ($permissionSlugs->contains(fn (string $slug) => in_array($slug, ['pages.create', 'pages.edit', 'pages.delete'], true))) {
            $scopes->push('content:write');
        }

        if ($permissionSlugs->contains(fn (string $slug) => in_array($slug, ['media.view', 'media.upload', 'media.edit', 'media.delete'], true))) {
            $scopes->push('media:read');
        }

        if ($permissionSlugs->contains(fn (string $slug) => in_array($slug, ['media.upload', 'media.edit', 'media.delete'], true))) {
            $scopes->push('media:write');
        }

        if ($permissionSlugs->contains('settings.view')) {
            $scopes->push('settings:read');
        }

        if ($permissionSlugs->contains('settings.edit')) {
            $scopes->push('settings:write');
        }

        if ($permissionSlugs->contains('system.view')) {
            $scopes->push('system:read');
        }

        if ($permissionSlugs->contains(fn (string $slug) => str_contains($slug, 'seo'))) {
            $scopes->push('seo:read');
        }

        return $scopes
            ->merge($permissionSlugs)
            ->unique()
            ->values()
            ->all();
    }
}
