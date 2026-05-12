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
             ['group_name' => 'site', 'setting_key' => 'admin_locale', 'setting_value' => 'ru', 'type' => 'string'],
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
             // Maintenance settings
             ['group_name' => 'maintenance', 'setting_key' => 'enabled', 'setting_value' => '0', 'type' => 'boolean'],
             ['group_name' => 'maintenance', 'setting_key' => 'theme', 'setting_value' => 'minimal', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'background_image', 'setting_value' => null, 'type' => 'integer'],
             ['group_name' => 'maintenance', 'setting_key' => 'background_blur', 'setting_value' => '0', 'type' => 'boolean'],
             ['group_name' => 'maintenance', 'setting_key' => 'logo', 'setting_value' => null, 'type' => 'integer'],
             ['group_name' => 'maintenance', 'setting_key' => 'primary_color', 'setting_value' => '#3b82f6', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'secondary_color', 'setting_value' => '#6b7280', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'title', 'setting_value' => 'Сайт на обслуживании', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'slogan', 'setting_value' => 'Мы обновляем сайт. Скоро вернемся!', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'text', 'setting_value' => '', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'show_login_form', 'setting_value' => '1', 'type' => 'boolean'],
             ['group_name' => 'maintenance', 'setting_key' => 'google_analytics_id', 'setting_value' => '', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'excluded_pages', 'setting_value' => '[]', 'type' => 'json'],
             ['group_name' => 'maintenance', 'setting_key' => 'cache_compatibility', 'setting_value' => '0', 'type' => 'boolean'],
             ['group_name' => 'maintenance', 'setting_key' => 'http_status_code', 'setting_value' => '503', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'allowed_ips', 'setting_value' => '', 'type' => 'string'],
             ['group_name' => 'maintenance', 'setting_key' => 'bypass_for_admins', 'setting_value' => '1', 'type' => 'boolean'],
         ];

        foreach ($defaults as $setting) {
            Setting::query()->updateOrCreate(
                ['group_name' => $setting['group_name'], 'setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
