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
            ['group_name' => 'site', 'setting_key' => 'locale', 'setting_value' => 'ru', 'type' => 'string'],
            ['group_name' => 'site', 'setting_key' => 'timezone', 'setting_value' => 'Europe/Moscow', 'type' => 'string'],
            ['group_name' => 'seo', 'setting_key' => 'sitemap_enabled', 'setting_value' => '1', 'type' => 'boolean'],
            ['group_name' => 'cache', 'setting_key' => 'enabled', 'setting_value' => '1', 'type' => 'boolean'],
        ];

        foreach ($defaults as $setting) {
            Setting::query()->updateOrCreate(
                ['group_name' => $setting['group_name'], 'setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}

