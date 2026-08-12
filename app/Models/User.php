<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'password_changed_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
    ];

    protected $casts = [
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_secret' => 'encrypted',
        'last_login_at' => 'datetime:Y-m-d H:i:s',
        'password_changed_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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

    /**
     * Check if two-factor authentication is enabled for this user.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret);
    }

    /**
     * Check if this user must use 2FA (based on role).
     */
    public function mustUseTwoFactor(): bool
    {
        $requiredRoles = config('security-login.login.2fa_required_for_roles', ['super-admin']);

        return $this->roles()
            ->whereIn('slug', $requiredRoles)
            ->exists();
    }

    /**
     * Get the number of active sessions for this user.
     */
    public function activeSessionCount(): int
    {
        $sessions = session()->get('user_sessions', []);

        return collect($sessions)->where('user_id', $this->id)->count();
    }
}
