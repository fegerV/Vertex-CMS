<?php

namespace Tests\Feature;

use App\Vertex\Security\Middleware\BasicRateLimiter;
use App\Vertex\Security\Middleware\SecureHeaders;
use App\Vertex\Security\Middleware\SessionGuard;
use Tests\TestCase;
use App\Vertex\Security\Support\ModuleRegistry;
use Illuminate\Http\Request;

class SecurityCoreArchitectureTest extends TestCase
{
    public function test_security_core_headers_are_applied(): void
    {
        config([
            'security.headers.csp' => "default-src 'self'",
        ]);

        $middleware = app(SecureHeaders::class);
        $request = Request::create('/security-test', 'GET');
        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertSame('camera=(), microphone=(), geolocation=()', $response->headers->get('Permissions-Policy'));
        $this->assertSame("default-src 'self'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_security_core_middleware_is_registered_in_kernel(): void
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $reflection = new \ReflectionObject($kernel);
        $property = $reflection->getProperty('middleware');
        $property->setAccessible(true);
        $middleware = $property->getValue($kernel);

        $this->assertContains(SecureHeaders::class, $middleware);
        $this->assertContains(SessionGuard::class, $middleware);
        $this->assertContains(BasicRateLimiter::class, $middleware);
    }

    public function test_security_module_registry_uses_config_toggles(): void
    {
        config([
            'security.modules.waf' => true,
            'security.modules.geoip' => false,
            'security.modules.integrity' => true,
        ]);

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->coreEnabled());
        $this->assertTrue($registry->isEnabled('waf'));
        $this->assertFalse($registry->isEnabled('geoip'));
        $this->assertSame([
            'waf' => true,
            'integrity' => true,
        ], array_intersect_key($registry->enabled(), [
            'waf' => true,
            'integrity' => true,
        ]));
    }
}
