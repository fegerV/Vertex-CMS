<?php

namespace Tests\Feature;

use App\Vertex\Security\Modules\Alerts\AlertsService;
use App\Vertex\Security\Modules\Integrity\IntegrityService;
use App\Vertex\Security\Modules\Scanner\ScannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_super_admin_can_view_security_dashboard(): void
    {
        config([
            'app.debug' => true,
            'security.modules.alerts' => true,
        ]);

        $superAdmin = $this->makeUserWithRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/system/security');

        $response->assertOk();
        $response->assertSee('Security Core');
        $response->assertSee('Integrity Monitor');
        $response->assertSee('Scanner');
        $response->assertSee('Real-time Alerts');
        $response->assertSee('APP_DEBUG');
    }

    public function test_viewer_cannot_view_security_dashboard(): void
    {
        $viewer = $this->makeUserWithRole('viewer');

        $this->actingAs($viewer)->get('/admin/system/security')->assertForbidden();
    }

    public function test_integrity_service_detects_file_drift_against_baseline(): void
    {
        $root = storage_path('framework/testing/integrity-'.Str::uuid());
        $trackedDirectory = $root.'/tracked';
        $metaDirectory = $root.'/meta';

        File::ensureDirectoryExists($trackedDirectory);
        File::ensureDirectoryExists($metaDirectory);

        file_put_contents($trackedDirectory.'/watched.txt', 'v1');

        config([
            'security.modules.integrity' => true,
            'security.integrity.tracked_paths' => [$trackedDirectory],
            'security.integrity.excluded_paths' => [],
            'security.integrity.baseline_path' => $metaDirectory.'/baseline.json',
            'security.integrity.report_path' => $metaDirectory.'/latest-report.json',
        ]);

        /** @var IntegrityService $service */
        $service = app(IntegrityService::class);

        $baseline = $service->initializeBaseline();

        $this->assertTrue($baseline['baseline_exists']);
        $this->assertSame('clean', $baseline['status']);

        file_put_contents($trackedDirectory.'/watched.txt', 'v2');

        $status = $service->runScan();

        $this->assertSame('drift_detected', $status['status']);
        $this->assertSame(1, $status['changed_count']);
        $this->assertSame(0, $status['added_count']);
        $this->assertSame(0, $status['removed_count']);
        $this->assertNotEmpty($status['recent_changes']);

        File::deleteDirectory($root);
    }

    public function test_super_admin_can_initialize_integrity_baseline_from_dashboard(): void
    {
        $superAdmin = $this->makeUserWithRole('super-admin');
        $root = storage_path('framework/testing/integrity-route-'.Str::uuid());
        $trackedDirectory = $root.'/tracked';
        $metaDirectory = $root.'/meta';

        File::ensureDirectoryExists($trackedDirectory);
        File::ensureDirectoryExists($metaDirectory);

        file_put_contents($trackedDirectory.'/watched.txt', 'v1');

        config([
            'security.modules.integrity' => true,
            'security.integrity.tracked_paths' => [$trackedDirectory],
            'security.integrity.excluded_paths' => [],
            'security.integrity.baseline_path' => $metaDirectory.'/baseline.json',
            'security.integrity.report_path' => $metaDirectory.'/latest-report.json',
        ]);

        $response = $this->actingAs($superAdmin)->post('/admin/system/security/integrity/baseline');

        $response->assertRedirect('/admin/system/security');
        $response->assertSessionHas('status');
        $this->assertFileExists($metaDirectory.'/baseline.json');
        $this->assertFileExists($metaDirectory.'/latest-report.json');

        File::deleteDirectory($root);
    }

    public function test_alerts_service_surfaces_live_configuration_warnings(): void
    {
        config([
            'app.debug' => true,
            'security.modules.alerts' => true,
            'security.audit.enabled' => false,
            'security.session.secure' => false,
            'security.modules.integrity' => false,
        ]);

        /** @var AlertsService $service */
        $service = app(AlertsService::class);
        $status = $service->getStatus();

        $this->assertTrue($status['enabled']);
        $this->assertSame('issues_detected', $status['status']);
        $this->assertGreaterThanOrEqual(3, $status['counts']['total']);
        $this->assertNotEmpty(
            collect($status['alerts'])->firstWhere('id', 'app-debug-enabled')
        );
        $this->assertNotEmpty(
            collect($status['alerts'])->firstWhere('id', 'audit-disabled')
        );
    }

    public function test_scanner_service_detects_suspicious_public_uploads(): void
    {
        $root = storage_path('framework/testing/scanner-'.Str::uuid());
        $uploadsDirectory = $root.'/uploads';
        $reportDirectory = $root.'/meta';

        File::ensureDirectoryExists($uploadsDirectory);
        File::ensureDirectoryExists($reportDirectory);

        file_put_contents($uploadsDirectory.'/shell.php', '<?php echo "danger";');

        config([
            'security.modules.scanner' => true,
            'security.scanner.paths' => [$uploadsDirectory],
            'security.scanner.report_path' => $reportDirectory.'/scanner-report.json',
            'vertex.uploads.path' => $uploadsDirectory,
        ]);

        /** @var ScannerService $service */
        $service = app(ScannerService::class);
        $status = $service->runScan();

        $this->assertSame('issues_detected', $status['status']);
        $this->assertSame(1, $status['counts']['danger']);
        $this->assertNotEmpty(
            collect($status['findings'])->firstWhere('type', 'executable-upload')
        );

        File::deleteDirectory($root);
    }

    public function test_alerts_service_surfaces_scanner_findings(): void
    {
        $root = storage_path('framework/testing/scanner-alerts-'.Str::uuid());
        $reportDirectory = $root.'/meta';

        File::ensureDirectoryExists($reportDirectory);

        file_put_contents($reportDirectory.'/scanner-report.json', json_encode([
            'scanned_at' => now()->toIso8601String(),
            'scanned_files' => 1,
            'counts' => [
                'danger' => 1,
                'warning' => 0,
            ],
            'findings' => [
                [
                    'type' => 'executable-upload',
                    'severity' => 'danger',
                    'path' => 'uploads/shell.php',
                    'message' => 'Executable file detected.',
                    'meta' => ['extension' => 'php'],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        config([
            'security.modules.alerts' => true,
            'security.modules.scanner' => true,
            'security.scanner.report_path' => $reportDirectory.'/scanner-report.json',
        ]);

        /** @var AlertsService $service */
        $service = app(AlertsService::class);
        $status = $service->getStatus();

        $this->assertSame('issues_detected', $status['status']);
        $this->assertNotEmpty(
            collect($status['alerts'])->firstWhere('id', 'scanner-issues-detected')
        );

        File::deleteDirectory($root);
    }
}
