<?php

namespace App\System\Services;

use App\Models\Page;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallerRunner
{
    public function __construct(
        private readonly EnvironmentFileService $environment,
        private readonly DatabaseConnectionService $database,
    ) {
    }

    public function run(array $payload): array
    {
        $databaseCheck = $this->database->check($payload);

        if (! $databaseCheck['ok']) {
            return $databaseCheck;
        }

        $this->environment->write($this->environmentValues($payload));
        $this->configureRuntimeDatabase($payload);

        if (blank(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);

        $admin = $this->createAdmin($payload);
        $this->seedSiteSettings($payload);
        $this->createHomePage($admin);
        $this->createLockFile($admin);

        return [
            'ok' => true,
            'message' => 'VertexCMS successfully installed.',
        ];
    }

    private function environmentValues(array $payload): array
    {
        return [
            'APP_NAME' => $payload['site_name'],
            'APP_URL' => $payload['site_url'],
            'APP_LOCALE' => $payload['site_locale'],
            'APP_TIMEZONE' => $payload['site_timezone'],
            'VERTEX_INSTALLED' => true,
            'MAIL_FROM_ADDRESS' => $payload['site_admin_email'],
            'MAIL_FROM_NAME' => $payload['site_name'],
            'DB_HOST' => $payload['DB_HOST'],
            'DB_PORT' => $payload['DB_PORT'],
            'DB_DATABASE' => $payload['DB_DATABASE'],
            'DB_USERNAME' => $payload['DB_USERNAME'],
            'DB_PASSWORD' => $payload['DB_PASSWORD'] ?? '',
        ];
    }

    private function configureRuntimeDatabase(array $payload): void
    {
        Config::set('database.connections.mysql.host', $payload['DB_HOST']);
        Config::set('database.connections.mysql.port', $payload['DB_PORT']);
        Config::set('database.connections.mysql.database', $payload['DB_DATABASE']);
        Config::set('database.connections.mysql.username', $payload['DB_USERNAME']);
        Config::set('database.connections.mysql.password', $payload['DB_PASSWORD'] ?? '');

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    private function createAdmin(array $payload): User
    {
        $admin = User::query()->updateOrCreate(
            ['email' => $payload['admin_email']],
            [
                'name' => $payload['admin_name'],
                'password' => Hash::make($payload['admin_password']),
                'status' => 'active',
            ],
        );

        $role = Role::query()->firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin'],
        );

        $admin->roles()->syncWithoutDetaching([$role->id]);

        return $admin;
    }

    private function seedSiteSettings(array $payload): void
    {
        $settings = [
            ['site', 'name', $payload['site_name'], 'string'],
            ['site', 'url', $payload['site_url'], 'string'],
            ['site', 'locale', $payload['site_locale'], 'string'],
            ['site', 'timezone', $payload['site_timezone'], 'string'],
            ['site', 'admin_email', $payload['site_admin_email'], 'string'],
        ];

        foreach ($settings as [$group, $key, $value, $type]) {
            Setting::query()->updateOrCreate(
                ['group_name' => $group, 'setting_key' => $key],
                ['setting_value' => $value, 'type' => $type, 'autoload' => true],
            );
        }
    }

    private function createHomePage(User $admin): void
    {
        Page::query()->firstOrCreate(
            ['uri' => '/'],
            [
                'title' => 'Главная',
                'slug' => 'home',
                'status' => 'published',
                'template' => 'default',
                'content_json' => [
                    'version' => '1.0',
                    'layout' => 'default',
                    'settings' => [
                        'container' => '1200px',
                        'background' => '#ffffff',
                    ],
                    'sections' => [],
                ],
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        );
    }

    private function createLockFile(User $admin): void
    {
        File::ensureDirectoryExists(dirname(config('vertex.install_lock_path')));

        File::put(config('vertex.install_lock_path'), json_encode([
            'installed_at' => now()->toISOString(),
            'installed_by' => $admin->id,
            'install_id' => (string) Str::uuid(),
            'version' => config('vertex.version'),
        ], JSON_PRETTY_PRINT));
    }
}
