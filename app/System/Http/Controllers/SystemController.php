<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function info(): View
    {
        return view('admin.system.info', [
            'info' => [
                'vertex_version' => config('vertex.version'),
                'php_version' => PHP_VERSION,
                'storage_writable' => is_writable(storage_path()),
                'uploads_writable' => is_writable(public_path('uploads')),
            ],
        ]);
    }

    public function logs(): View
    {
        return view('admin.system.logs', [
            'logs' => ActivityLog::query()->latest('created_at')->paginate(50),
        ]);
    }

    public function clearCache(): RedirectResponse
    {
        return redirect()->back();
    }
}

