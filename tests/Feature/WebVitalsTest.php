<?php

namespace Tests\Feature;

use App\Models\WebVitalMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebVitalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_anonymous_visitor_can_report_a_metric_from_a_public_page(): void
    {
        $response = $this->postJson('/api/v1/public/web-vitals', [
            'metric_type' => 'LCP',
            'value' => 1.25,
            'url' => 'https://example.test/about?utm_source=newsletter#team',
            'session_id' => 'sess_test_1',
            'metadata' => [
                'userAgent' => 'Test Agent',
                'viewportSize' => '1280x720',
                'injected' => 'should be dropped',
            ],
        ]);

        $response->assertCreated();
        $response->assertJson(['success' => true, 'rating' => 'good']);

        $metric = WebVitalMetric::query()->sole();

        $this->assertSame('LCP', $metric->metric_type);
        $this->assertSame('good', $metric->rating);
        $this->assertSame('https://example.test/about', $metric->url);
        $this->assertSame(['userAgent' => 'Test Agent', 'viewportSize' => '1280x720'], $metric->metadata);
    }

    public function test_metric_rating_follows_the_thresholds_of_the_model(): void
    {
        $this->postJson('/api/v1/public/web-vitals', [
            'metric_type' => 'LCP',
            'value' => 5.5,
        ])->assertCreated()->assertJson(['rating' => 'poor']);

        $this->postJson('/api/v1/public/web-vitals', [
            'metric_type' => 'CLS',
            'value' => 0.18,
        ])->assertCreated()->assertJson(['rating' => 'needs-improvement']);

        $this->assertSame(2, WebVitalMetric::query()->count());
    }

    public function test_public_endpoint_rejects_unknown_metric_types_and_negative_values(): void
    {
        $this->postJson('/api/v1/public/web-vitals', [
            'metric_type' => 'SCRIPT',
            'value' => 1,
        ])->assertStatus(422);

        $this->postJson('/api/v1/public/web-vitals', [
            'metric_type' => 'LCP',
            'value' => -1,
        ])->assertStatus(422);

        $this->postJson('/api/v1/public/web-vitals', [
            'value' => 1,
        ])->assertStatus(422);

        $this->assertSame(0, WebVitalMetric::query()->count());
    }

    public function test_admin_without_system_permission_cannot_open_the_dashboard(): void
    {
        $viewer = $this->makeUserWithRole('viewer');

        $this->actingAs($viewer)->get('/admin/web-vitals')->assertForbidden();
    }

    public function test_super_admin_sees_the_dashboard_with_stored_metrics(): void
    {
        WebVitalMetric::query()->create([
            'metric_type' => 'LCP',
            'value' => 1.2,
            'rating' => 'good',
            'measured_at' => now(),
        ]);

        $superAdmin = $this->makeUserWithRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/web-vitals');

        $response->assertOk();
        $response->assertSee('Largest Contentful Paint');
    }

    public function test_super_admin_can_export_metrics_as_csv(): void
    {
        WebVitalMetric::query()->create([
            'metric_type' => 'INP',
            'value' => 0.15,
            'rating' => 'good',
            'url' => 'https://example.test/',
            'measured_at' => now(),
        ]);

        $superAdmin = $this->makeUserWithRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/web-vitals/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Metric Type', $csv);
        $this->assertStringContainsString('INP', $csv);
    }
}
