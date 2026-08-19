<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Modules\Security\Models\IpFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_add_ip_to_blacklist(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/api/security/ip-filter', [
                'ip' => '192.168.1.100',
                'type' => 'blacklist',
                'reason' => 'Suspicious activity'
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('ip_filters', [
            'ip' => '192.168.1.100',
            'type' => 'blacklist'
        ]);
    }

    public function test_admin_can_add_ip_to_whitelist(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/api/security/ip-filter', [
                'ip' => '10.0.0.1',
                'type' => 'whitelist',
                'reason' => 'Trusted network'
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('ip_filters', [
            'ip' => '10.0.0.1',
            'type' => 'whitelist'
        ]);
    }

    public function test_non_admin_cannot_manage_ip_filters(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)
            ->postJson('/admin/api/security/ip-filter', [
                'ip' => '192.168.1.100',
                'type' => 'blacklist'
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_ip_filter(): void
    {
        $filter = IpFilter::create([
            'ip' => '192.168.1.100',
            'type' => 'blacklist',
            'reason' => 'Test'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/admin/api/security/ip-filter/{$filter->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('ip_filters', [
            'id' => $filter->id
        ]);
    }
}
