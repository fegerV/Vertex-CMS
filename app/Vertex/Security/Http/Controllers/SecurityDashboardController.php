<?php

namespace App\Vertex\Security\Http\Controllers;

use App\Http\Controllers\Controller;
use App\System\Services\ActivityLogService;
use App\Vertex\Security\Modules\Integrity\IntegrityService;
use App\Vertex\Security\Services\SecurityDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityDashboardController extends Controller
{
    public function __construct(
        private readonly SecurityDashboardService $dashboard,
        private readonly IntegrityService $integrity,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function index(): View
    {
        return view('admin.system.security', [
            'security' => $this->dashboard->overview(),
        ]);
    }

    public function initializeIntegrity(Request $request): RedirectResponse
    {
        $status = $this->integrity->initializeBaseline();

        $this->activityLog->record(
            'security.integrity.initialize',
            'security_integrity',
            null,
            'Integrity baseline initialized.',
            [
                'tracked_files' => $status['tracked_files'] ?? 0,
            ],
            $request,
        );

        return redirect()
            ->route('admin.system.security')
            ->with('status', 'Integrity baseline initialized.');
    }

    public function scanIntegrity(Request $request): RedirectResponse
    {
        $status = $this->integrity->runScan();

        $this->activityLog->record(
            'security.integrity.scan',
            'security_integrity',
            null,
            'Integrity scan finished.',
            [
                'status' => $status['status'] ?? 'unknown',
                'changed_count' => $status['changed_count'] ?? 0,
                'added_count' => $status['added_count'] ?? 0,
                'removed_count' => $status['removed_count'] ?? 0,
            ],
            $request,
        );

        return redirect()
            ->route('admin.system.security')
            ->with('status', 'Integrity scan completed.');
    }
}
