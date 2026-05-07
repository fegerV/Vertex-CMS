<?php

namespace App\Seo\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

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
}

