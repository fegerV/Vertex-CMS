<?php

namespace Tests\Feature;

use App\Security\Login\Support\TwoFactorSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
    }

    public function test_active_admin_signs_in_and_reaches_the_dashboard(): void
    {
        $admin = $this->makeUserWithRole('super-admin');

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->assertTrue(session(TwoFactorSession::VERIFIED));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_inactive_admin_cannot_sign_in(): void
    {
        $admin = $this->makeUserWithRole('super-admin', ['status' => 'inactive']);

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guests_are_redirected_and_logout_ends_the_session(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $admin = $this->makeUserWithRole('super-admin');
        $this->actingAs($admin);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}
