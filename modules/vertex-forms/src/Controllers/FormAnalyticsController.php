<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Services\FormAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormAnalyticsController extends Controller
{
    public function __construct(
        private readonly FormAnalyticsService $analyticsService
    ) {}

    /**
     * Show analytics summary.
     */
    public function show(Form $form, Request $request): JsonResponse
    {
        $days = $request->integer("days", 30);
        $data = $this->analyticsService->getAnalytics($form, $days);

        return response()->json(["analytics" => $data]);
    }

    /**
     * Return time-series data for charts.
     */
    public function data(Form $form, Request $request): JsonResponse
    {
        $days = $request->integer("days", 30);
        $data = $this->analyticsService->getAnalytics($form, $days);

        return response()->json([
            "dates" => $data["daily"]["dates"],
            "submissions" => $data["daily"]["submissions"],
            "fields_completion" => $data["fields_completion"],
        ]);
    }
}
