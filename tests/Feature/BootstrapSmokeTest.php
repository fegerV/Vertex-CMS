<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the application bootstrap: these routes touch the middleware stack,
 * the view layer and the API group, so a broken service provider, facade alias
 * or missing vendor package surfaces here instead of during a manual check.
 */
class BootstrapSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_health_endpoint_answers(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_installer_is_locked_once_installed(): void
    {
        $this->get('/install')->assertForbidden();
    }

    public function test_public_api_v1_answers(): void
    {
        $this->get('/api/v1/public/pages')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_frontend_home_page_renders(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('name="password"', false);
    }

    public function test_admin_shell_redirects_guests_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }
}
