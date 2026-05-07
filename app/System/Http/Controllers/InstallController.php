<?php

namespace App\System\Http\Controllers;

use App\Core\Services\InstallationService;
use App\Http\Controllers\Controller;
use App\System\Services\DatabaseConnectionService;
use App\System\Services\EnvironmentFileService;
use App\System\Services\InstallerRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class InstallController extends Controller
{
    public function __construct(
        private readonly InstallationService $installation,
        private readonly DatabaseConnectionService $database,
        private readonly EnvironmentFileService $environment,
        private readonly InstallerRunner $installer,
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
        $payload = $request->validate($this->databaseRules());
        $result = $this->database->check($payload);

        return response()->json([
            'ok' => $result['ok'],
            'message' => $result['message'],
        ], $result['ok'] ? 200 : 422);
    }

    public function saveConfig(Request $request): JsonResponse
    {
        $payload = $request->validate($this->configurationRules());

        $this->environment->write([
            'APP_NAME' => $payload['site_name'],
            'APP_URL' => $payload['site_url'],
            'APP_LOCALE' => $payload['site_locale'],
            'APP_TIMEZONE' => $payload['site_timezone'],
            'MAIL_FROM_ADDRESS' => $payload['site_admin_email'],
            'MAIL_FROM_NAME' => $payload['site_name'],
            'DB_HOST' => $payload['DB_HOST'],
            'DB_PORT' => $payload['DB_PORT'],
            'DB_DATABASE' => $payload['DB_DATABASE'],
            'DB_USERNAME' => $payload['DB_USERNAME'],
            'DB_PASSWORD' => $payload['DB_PASSWORD'] ?? '',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Configuration saved.',
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $requirements = $this->installation->requirements();

        if (in_array(false, $requirements, true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Server requirements are not satisfied.',
                'requirements' => $requirements,
            ], 422);
        }

        $payload = $request->validate($this->installationRules());

        try {
            $result = $this->installer->run($payload);

            return response()->json($result, $result['ok'] ? 200 : 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => 'Installation failed.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    private function databaseRules(): array
    {
        return [
            'DB_HOST' => ['required', 'string', 'max:255'],
            'DB_PORT' => ['required', 'integer', 'between:1,65535'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => ['required', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function configurationRules(): array
    {
        return array_merge($this->databaseRules(), [
            'site_name' => ['required', 'string', 'max:255'],
            'site_url' => ['required', 'url', 'max:500'],
            'site_locale' => ['required', 'string', 'max:20'],
            'site_timezone' => ['required', 'timezone'],
            'site_admin_email' => ['required', 'email', 'max:255'],
        ]);
    }

    private function installationRules(): array
    {
        return array_merge($this->configurationRules(), [
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
