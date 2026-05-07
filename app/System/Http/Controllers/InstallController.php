<?php

namespace App\System\Http\Controllers;

use App\Core\Services\InstallationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(
        private readonly InstallationService $installation,
    ) {
    }

    public function index(): View
    {
        return view('installer.index', [
            'requirements' => $this->installation->requirements(),
        ]);
    }

    public function checkRequirements(): JsonResponse
    {
        $requirements = $this->installation->requirements();

        return response()->json([
            'ok' => ! in_array(false, $requirements, true),
            'requirements' => $requirements,
        ]);
    }

    public function checkDatabase(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Database validation placeholder',
            'payload' => $request->only(['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME']),
        ]);
    }

    public function saveConfig(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Configuration persistence placeholder',
            'payload' => $request->all(),
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => 'Installer execution placeholder',
            'payload' => $request->all(),
        ]);
    }
}

