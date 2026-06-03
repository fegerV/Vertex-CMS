<?php

namespace App\System\Http\Controllers;

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
            'dashboard' => $this->dashboard->overview(),
        ]);
    }

    public function initializeIntegrityBaseline(Request $request): RedirectResponse
    {
        if (! config('security.modules.integrity', false)) {
            return redirect()
                ->route('admin.system.security')
                ->with('status', 'Integrity Monitor выключен в конфигурации.');
        }

        $status = $this->integrity->initializeBaseline();

        $this->activityLog->record(
            'security.integrity.baseline.initialize',
            'security_integrity',
            null,
            'Integrity baseline initialized.',
            [
                'tracked_files' => $status['tracked_files'] ?? 0,
                'status' => $status['status'] ?? 'unknown',
            ],
            $request,
        );

        return redirect()
            ->route('admin.system.security')
            ->with('status', 'Baseline для Integrity Monitor успешно сохранен.');
    }

    public function runIntegrityScan(Request $request): RedirectResponse
    {
        if (! config('security.modules.integrity', false)) {
            return redirect()
                ->route('admin.system.security')
                ->with('status', 'Integrity Monitor выключен в конфигурации.');
        }

        $status = $this->integrity->runScan();

        $this->activityLog->record(
            'security.integrity.scan',
            'security_integrity',
            null,
            'Integrity scan completed.',
            [
                'tracked_files' => $status['tracked_files'] ?? 0,
                'changed_count' => $status['changed_count'] ?? 0,
                'added_count' => $status['added_count'] ?? 0,
                'removed_count' => $status['removed_count'] ?? 0,
                'status' => $status['status'] ?? 'unknown',
            ],
            $request,
        );

        return redirect()
            ->route('admin.system.security')
            ->with('status', 'Сканирование целостности завершено.');
    }
}
