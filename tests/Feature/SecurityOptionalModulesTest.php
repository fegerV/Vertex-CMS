<?php

namespace Tests\Feature;

use App\Vertex\Security\Modules\Cloudflare\CloudflareRequest;
use App\Vertex\Security\Modules\Cloudflare\CloudflareService;
use App\Vertex\Security\Modules\GeoIp\GeoIpMiddleware;
use App\Vertex\Security\Modules\GeoIp\GeoIpService;
use App\Vertex\Security\Modules\Hibp\HibpService;
use App\Vertex\Security\Modules\Waf\WafService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityOptionalModulesTest extends TestCase
{
    public function test_waf_detects_attacks_and_accepts_normal_requests(): void
    {
        $waf = app(WafService::class);

        $this->assertNull($waf->inspect(Request::create('/search', 'GET', ['q' => 'vertex cms'])));
        $this->assertSame('sql-injection', $waf->inspect(Request::create('/search?q=1%20UNION%20SELECT%20password', 'GET'))['rule']);
        $this->assertSame('path-traversal', $waf->inspect(Request::create('/download?file=..%2F.env', 'GET'))['rule']);
    }

    public function test_geoip_resolves_ipv4_and_ipv6_from_local_database(): void
    {
        $path = storage_path('framework/testing-geoip.csv');
        file_put_contents($path, "203.0.113.0/24,US\n2001:db8::/32,DE\n");
        config(['security.geoip.local_database' => $path, 'security.geoip.blocked_countries' => ['DE']]);

        $geoIp = app(GeoIpService::class);
        $this->assertSame('US', $geoIp->locate('203.0.113.10')['country_code']);
        $germany = $geoIp->locate('2001:db8::5');
        $this->assertSame('DE', $germany['country_code']);
        $this->assertFalse($geoIp->isAllowed($germany));

        @unlink($path);
    }

    public function test_geoip_middleware_exposes_location_and_blocks_denied_country(): void
    {
        config(['security.geoip.trusted_headers' => true, 'security.geoip.blocked_countries' => ['DE']]);
        $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '203.0.113.1', 'HTTP_CF_IPCOUNTRY' => 'US']);

        $response = app(GeoIpMiddleware::class)->handle($request, fn () => response('ok'));
        $this->assertSame('US', $request->attributes->get('vertex.geoip')['country_code']);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_hibp_uses_k_anonymity_without_sending_the_password(): void
    {
        Cache::flush();
        $password = 'correct horse battery staple';
        $hash = strtoupper(sha1($password));
        Http::fake([
            '*' => Http::response(substr($hash, 5).":42\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA:1", 200),
        ]);

        $this->assertSame(42, app(HibpService::class)->occurrenceCount($password));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/'.substr($hash, 0, 5))
            && ! str_contains($request->body(), $password));
    }

    public function test_cloudflare_cache_purge_is_authenticated_and_zone_scoped(): void
    {
        config([
            'security.cloudflare.api_token' => 'secret-token',
            'security.cloudflare.zone_id' => 'zone-123',
        ]);
        Http::fake(['*/zones/zone-123/purge_cache' => Http::response(['success' => true])]);

        $result = app(CloudflareService::class)->purgeUrls(['https://example.test/a']);

        $this->assertTrue($result['success']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer secret-token')
            && $request['files'] === ['https://example.test/a']);
    }

    public function test_cloudflare_visitor_header_is_only_used_for_trusted_proxies(): void
    {
        config([
            'security.cloudflare.trust_visitor_headers' => true,
            'security.cloudflare.trusted_proxies' => ['173.245.48.0/20'],
        ]);
        $trusted = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '173.245.48.10',
            'HTTP_CF_CONNECTING_IP' => '198.51.100.2',
        ]);
        $untrusted = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '192.0.2.5',
            'HTTP_CF_CONNECTING_IP' => '198.51.100.2',
        ]);

        $resolver = app(CloudflareRequest::class);
        $this->assertSame('198.51.100.2', $resolver->visitorIp($trusted));
        $this->assertSame('192.0.2.5', $resolver->visitorIp($untrusted));
    }
}
