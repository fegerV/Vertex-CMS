<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders_the_login_form(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }
}
