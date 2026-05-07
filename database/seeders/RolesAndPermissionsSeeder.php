<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'pages.view',
            'pages.create',
            'pages.edit',
            'pages.delete',
            'pages.publish',
            'media.view',
            'media.upload',
            'media.edit',
            'media.delete',
            'seo.view',
            'seo.edit',
            'settings.view',
            'settings.edit',
            'system.view',
            'cache.clear',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(
                ['slug' => $permission],
                ['name' => $permission, 'group_name' => explode('.', $permission)[0]]
            );
        }

        $roles = [
            'super-admin' => 'Super Admin',
            'admin' => 'Admin',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
        ];

        $permissionIds = Permission::query()->pluck('id', 'slug');

        $rolePermissions = [
            'super-admin' => $permissionIds->keys()->all(),
            'admin' => [
                'admin.access',
                'pages.view',
                'pages.create',
                'pages.edit',
                'pages.delete',
                'pages.publish',
                'media.view',
                'media.upload',
                'media.edit',
                'media.delete',
                'seo.view',
                'seo.edit',
                'settings.view',
                'settings.edit',
                'system.view',
                'cache.clear',
            ],
            'editor' => [
                'admin.access',
                'pages.view',
                'pages.create',
                'pages.edit',
                'pages.publish',
                'media.view',
                'media.upload',
                'media.edit',
                'seo.view',
                'seo.edit',
            ],
            'viewer' => [
                'admin.access',
                'pages.view',
                'media.view',
                'seo.view',
                'settings.view',
                'system.view',
            ],
        ];

        foreach ($roles as $slug => $name) {
            $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => $name]);

            $role->permissions()->sync(
                collect($rolePermissions[$slug] ?? [])
                    ->map(fn (string $permission) => $permissionIds[$permission] ?? null)
                    ->filter()
                    ->values()
                    ->all()
            );
        }
    }
}
