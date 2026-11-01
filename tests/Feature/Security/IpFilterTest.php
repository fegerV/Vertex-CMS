<?php

namespace Tests\Feature\Security;

use App\Models\IpFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();

        // ip-filter management requires settings.edit, granted to super-admin.
        $this->adminUser = $this->makeUserWithRole('super-admin');
    }

    public function test_admin_can_add_ip_to_blacklist(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/security/ip-filters', [
                'ip_address' => '192.168.1.100',
                'type' => 'blacklist',
                'reason' => 'Suspicious activity',
            ]);

        $response->assertRedirect(route('admin.security.ip-filters'));

        $this->assertDatabaseHas('ip_filters', [
            'ip_address' => '192.168.1.100',
            'type' => 'blacklist',
        ]);
    }

    public function test_admin_can_add_ip_to_whitelist(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/admin/security/ip-filters', [
                'ip_address' => '10.0.0.1',
                'type' => 'whitelist',
                'reason' => 'Trusted network',
            ])
            ->assertRedirect(route('admin.security.ip-filters'));

        $this->assertDatabaseHas('ip_filters', [
            'ip_address' => '10.0.0.1',
            'type' => 'whitelist',
        ]);
    }

    public function test_non_admin_cannot_manage_ip_filters(): void
    {
        $editor = $this->makeUserWithRole('editor', ['email' => 'editor-ip@example.com']);

        $this->actingAs($editor)
            ->post('/admin/security/ip-filters', [
                'ip_address' => '192.168.1.100',
                'type' => 'blacklist',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('ip_filters', ['ip_address' => '192.168.1.100']);
    }

    public function test_admin_can_delete_ip_filter(): void
    {
        $filter = IpFilter::create([
            'ip_address' => '192.168.1.100',
            'type' => 'blacklist',
            'reason' => 'Test',
        ]);

        $this->actingAs($this->adminUser)
            ->delete("/admin/security/ip-filters/{$filter->id}")
            ->assertRedirect(route('admin.security.ip-filters'));

        $this->assertDatabaseMissing('ip_filters', ['id' => $filter->id]);
    }
}
