<?php

namespace Tests\Feature;

use App\Core\Services\InstallationService;
use App\System\Services\InstallerRunner;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class InstallerAccessTest extends TestCase
{
    public function test_installer_rejects_invalid_configuration_without_running(): void
    {
        $this->mock(InstallationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isInstalled')->andReturnFalse();
            $mock->shouldReceive('requirements')->once()->andReturn(['php' => true]);
        });
        $this->mock(InstallerRunner::class, fn (MockInterface $mock) => $mock->shouldNotReceive('run'));

        $this->postJson('/install/run', [
            'site_name' => 'Vertex',
            'site_url' => 'not-a-url',
        ])->assertRedirect()->assertSessionHasErrors([
            'site_url', 'site_locale', 'site_timezone', 'site_admin_email',
            'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
            'admin_name', 'admin_email', 'admin_password',
        ]);
    }

    public function test_installer_does_not_disclose_internal_exception_messages(): void
    {
        $this->mock(InstallationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isInstalled')->andReturnFalse();
            $mock->shouldReceive('requirements')->once()->andReturn(['php' => true]);
        });
        $this->mock(InstallerRunner::class, function (MockInterface $mock): void {
            $mock->shouldReceive('run')->once()->andThrow(new RuntimeException('password=top-secret'));
        });

        $response = $this->postJson('/install/run', $this->validPayload());

        $response->assertStatus(500)->assertExactJson([
            'ok' => false,
            'message' => 'Installation failed.',
        ]);
        $this->assertStringNotContainsString('top-secret', $response->getContent());
    }

    private function validPayload(): array
    {
        return [
            'site_name' => 'Vertex', 'site_url' => 'https://example.test',
            'site_locale' => 'en', 'site_timezone' => 'UTC', 'site_admin_email' => 'owner@example.test',
            'DB_HOST' => '127.0.0.1', 'DB_PORT' => 3306, 'DB_DATABASE' => 'vertex', 'DB_USERNAME' => 'vertex', 'DB_PASSWORD' => '',
            'admin_name' => 'Owner', 'admin_email' => 'owner@example.test',
            'admin_password' => 'long-password', 'admin_password_confirmation' => 'long-password',
        ];
    }
}
