<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Analytics\Heatmap;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HeatmapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Heatmap::query()->where('user_id', $user->id);

        if ($request->has('page_url')) {
            $query->where('page_url', $request->page_url);
        }

        if ($request->has('type')) {
            $query->where('heatmap_type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->where('date_range_start', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date_range_end', '<=', $request->date_to);
        }

        $heatmaps = $query->paginate(20);

        return response()->json($heatmaps);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_url' => 'required|url',
            'heatmap_type' => 'required|in:click,move,scroll',
            'data_points' => 'required|array',
            'viewport_width' => 'required|integer',
            'viewport_height' => 'required|integer',
        ]);

        $heatmap = Heatmap::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($heatmap, 201);
    }

    public function show(Request $request, Heatmap $heatmap): JsonResponse
    {
        // Проверка владения: пользователь может получить только свои heatmap
        abort_unless((int) $heatmap->user_id === (int) $request->user()->id, 403);
        
        return response()->json($heatmap);
    }

    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'type' => 'required|in:click,move,scroll',
            'points' => 'required|array',
            'viewport' => 'required|array',
            'viewport.width' => 'required|integer',
            'viewport.height' => 'required|integer',
        ]);

        // Здесь можно добавить запись в очередь для обработки
        dispatch(new \App\Jobs\ProcessHeatmapData(
            $validated['url'],
            $validated['type'],
            $validated['points'],
            $validated['viewport']['width'],
            $validated['viewport']['height']
        ));

        return response()->json(['success' => true], 202);
    }
}
