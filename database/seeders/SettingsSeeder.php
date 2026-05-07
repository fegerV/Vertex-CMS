<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['group_name' => 'site', 'setting_key' => 'name', 'setting_value' => 'VertexCMS', 'type' => 'string'],
            ['group_name' => 'site', 'setting_key' => 'description', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'site', 'setting_key' => 'locale', 'setting_value' => 'ru', 'type' => 'string'],
            ['group_name' => 'site', 'setting_key' => 'timezone', 'setting_value' => 'Europe/Moscow', 'type' => 'string'],
            ['group_name' => 'api', 'setting_key' => 'public_enabled', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'api', 'setting_key' => 'mobile_enabled', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'api', 'setting_key' => 'version', 'setting_value' => 'v1', 'type' => 'string'],
            ['group_name' => 'api', 'setting_key' => 'rate_limit_public', 'setting_value' => '120', 'type' => 'integer'],
            ['group_name' => 'api', 'setting_key' => 'rate_limit_mobile', 'setting_value' => '300', 'type' => 'integer'],
            ['group_name' => 'seo', 'setting_key' => 'sitemap_enabled', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'seo', 'setting_key' => 'robots_enabled', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'cache', 'setting_key' => 'enabled', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'cache', 'setting_key' => 'driver', 'setting_value' => 'file', 'type' => 'string'],
            ['group_name' => 'cache', 'setting_key' => 'ttl', 'setting_value' => '3600', 'type' => 'integer'],
            ['group_name' => 'cache', 'setting_key' => 'html_minify', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'ai', 'setting_key' => 'enabled', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'ai', 'setting_key' => 'default_provider', 'setting_value' => 'openai', 'type' => 'string'],
            ['group_name' => 'ai', 'setting_key' => 'default_model', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'ai', 'setting_key' => 'allow_editor_use', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'pwa', 'setting_key' => 'enabled', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'pwa', 'setting_key' => 'name', 'setting_value' => 'VertexCMS', 'type' => 'string'],
            ['group_name' => 'pwa', 'setting_key' => 'short_name', 'setting_value' => 'VertexCMS', 'type' => 'string'],
            ['group_name' => 'pwa', 'setting_key' => 'theme_color', 'setting_value' => '#020617', 'type' => 'string'],
            ['group_name' => 'pwa', 'setting_key' => 'background_color', 'setting_value' => '#ffffff', 'type' => 'string'],
            ['group_name' => 'pwa', 'setting_key' => 'display', 'setting_value' => 'standalone', 'type' => 'string'],
            ['group_name' => 'pwa', 'setting_key' => 'start_url', 'setting_value' => '/', 'type' => 'string'],
        ];

        foreach ($defaults as $setting) {
            Setting::query()->updateOrCreate(
                ['group_name' => $setting['group_name'], 'setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
