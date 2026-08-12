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
             ['group_name' => 'design', 'setting_key' => 'primary_color', 'setting_value' => '#0f766e', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'secondary_color', 'setting_value' => '#334155', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'accent_color', 'setting_value' => '#f59e0b', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'text_color', 'setting_value' => '#0f172a', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'background_color', 'setting_value' => '#ffffff', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'heading_font', 'setting_value' => 'Manrope', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'body_font', 'setting_value' => 'Manrope', 'type' => 'string'],
             ['group_name' => 'design', 'setting_key' => 'base_font_size', 'setting_value' => '16', 'type' => 'integer'],
             ['group_name' => 'design', 'setting_key' => 'content_width', 'setting_value' => '1200', 'type' => 'integer'],
             ['group_name' => 'design', 'setting_key' => 'section_spacing', 'setting_value' => '64', 'type' => 'integer'],
             ['group_name' => 'design', 'setting_key' => 'button_radius', 'setting_value' => '8', 'type' => 'integer'],
             ['group_name' => 'design', 'setting_key' => 'button_weight', 'setting_value' => '600', 'type' => 'integer'],
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
            // Mail settings
            ['group_name' => 'mail', 'setting_key' => 'driver', 'setting_value' => 'log', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'host', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'port', 'setting_value' => '587', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'encryption', 'setting_value' => 'tls', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'username', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'password', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'from_address', 'setting_value' => 'noreply@example.com', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'from_name', 'setting_value' => config_value('site.name', 'VertexCMS'), 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'reply_to_address', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'reply_to_name', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'test_recipient', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'mail', 'setting_key' => 'queue_enabled', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'mail', 'setting_key' => 'retry_attempts', 'setting_value' => '3', 'type' => 'integer'],
            ['group_name' => 'mail', 'setting_key' => 'log_all', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'mail', 'setting_key' => 'attach_assets', 'setting_value' => '0', 'type' => 'boolean'],
            // Telegram widget
            ['group_name' => 'telegram', 'setting_key' => 'enabled', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'telegram', 'setting_key' => 'username', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'bot_token', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'chat_id', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'widget_style', 'setting_value' => 'floating', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'widget_position', 'setting_value' => 'bottom-right', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'greeting', 'setting_value' => '', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'color', 'setting_value' => '#0088cc', 'type' => 'string'],
            ['group_name' => 'telegram', 'setting_key' => 'show_online_status', 'setting_value' => '0', 'type' => 'boolean'],
            ['group_name' => 'telegram', 'setting_key' => 'message_prefill', 'setting_value' => '', 'type' => 'string'],
         ];

        foreach ($defaults as $setting) {
            Setting::query()->updateOrCreate(
                ['group_name' => $setting['group_name'], 'setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
