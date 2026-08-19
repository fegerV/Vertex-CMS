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
            'media.manage_folders',
            'ecommerce.products.view',
            'ecommerce.products.create',
            'ecommerce.products.edit',
            'ecommerce.products.delete',
            'ecommerce.orders.view',
            'ecommerce.orders.update',
            'ecommerce.payments.update',
            'forms.view',
            'forms.create',
            'forms.edit',
            'forms.delete',
            'forms.view_submissions',
            'forms.view_analytics',
            'forms.import_export',
            'forms.manage_settings',
            'seo.view',
            'seo.edit',
            'analytics.view',
            'taxonomy.view',
            'taxonomy.create',
            'taxonomy.edit',
            'taxonomy.delete',
            'ai.view',
            'ai.use',
            'ai.manage_providers',
            'ai.manage_keys',
            'ai.view_logs',
            'settings.view',
            'settings.edit',
            'system.view',
            'cache.clear',
            'mail.view',
            'mail.edit',
            'mail.delete',
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
                'media.manage_folders',
                'ecommerce.products.view',
                'ecommerce.products.create',
                'ecommerce.products.edit',
                'ecommerce.products.delete',
                'ecommerce.orders.view',
                'ecommerce.orders.update',
                'ecommerce.payments.update',
                'forms.view',
                'forms.create',
                'forms.edit',
                'forms.delete',
                'forms.view_submissions',
                'forms.view_analytics',
                'forms.import_export',
                'forms.manage_settings',
                'seo.view',
                'seo.edit',
                'analytics.view',
                'taxonomy.view',
                'taxonomy.create',
                'taxonomy.edit',
                'taxonomy.delete',
                'ai.view',
                'ai.use',
                'ai.view_logs',
                'settings.view',
                'settings.edit',
                'mail.view',
                'mail.edit',
                'mail.delete',
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
                'forms.view',
                'forms.create',
                'forms.edit',
                'forms.view_submissions',
                'seo.view',
                'seo.edit',
                'analytics.view',
                'taxonomy.view',
                'ai.view',
                'ai.use',
            ],
            'viewer' => [
                'admin.access',
                'pages.view',
                'media.view',
                'seo.view',
                'analytics.view',
                'settings.view',
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
