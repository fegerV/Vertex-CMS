<?php

namespace App\Seo\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RobotsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings
    ) {}

    public function index(): Response
    {
        $content = $this->settings->get('seo.robots_txt');

        return response(view('frontend.robots', compact('content')))
            ->header('Content-Type', 'text/plain');
    }

    public function edit(): View
    {
        $robotsContent = $this->settings->get('seo.robots_txt') ?? '';
        $htaccessContent = '';
        
        $htaccessPath = base_path('.htaccess');
        if (file_exists($htaccessPath)) {
            $htaccessContent = file_get_contents($htaccessPath);
        }

        return view('admin.seo.files', [
            'robotsContent' => $robotsContent,
            'htaccessContent' => $htaccessContent,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'robots_txt' => 'nullable|string',
            'htaccess' => 'nullable|string',
        ]);

        if ($request->filled('robots_txt')) {
            $this->settings->set('seo.robots_txt', $request->robots_txt);
        }

        if ($request->filled('htaccess')) {
            $htaccessPath = base_path('.htaccess');
            file_put_contents($htaccessPath, $request->htaccess);
        }

        return redirect()->route('admin.seo.files')->with('success', 'Файлы успешно обновлены');
    }
}

