<?php

namespace Tests\Feature;

use App\Core\Services\SettingsService;
use App\Core\Support\SettingCatalog;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RobotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_returns_database_setting()
    {
        $settings = app(SettingsService::class);
        $settings->setMany(['seo.robots_txt' => "User-agent: *\nDisallow: /admin"]);

        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee("User-agent: *\nDisallow: /admin");
    }

    public function test_robots_txt_returns_default_when_setting_empty()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('User-agent: *');
        $response->assertSee('Allow: /');
        $response->assertSee('Sitemap:');
    }
}
