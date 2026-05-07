<?php

namespace App\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\Page;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'siteName' => config_value('site.name', config('app.name')),
            'stats' => [
                'pages' => Page::query()->count(),
                'published_pages' => Page::query()->where('status', 'published')->count(),
                'draft_pages' => Page::query()->where('status', 'draft')->count(),
                'media_files' => Media::query()->count(),
            ],
            'recentActivity' => ActivityLog::query()->latest('created_at')->limit(10)->get(),
        ]);
    }
}

