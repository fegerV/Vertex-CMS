<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Seo\Services\SeoAuditService;
use Illuminate\View\View;

class SeoDashboardController extends Controller
{
    public function __construct(
        private readonly SeoAuditService $audit,
    ) {
    }

    public function index(): View
    {
        return view('admin.seo.dashboard', [
            'dashboard' => $this->audit->overview(),
        ]);
    }
}
