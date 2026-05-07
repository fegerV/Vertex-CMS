<?php

namespace App\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Page::query()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'payload' => $request->all()], 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json($page);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        return response()->json(['ok' => true, 'id' => $page->id, 'payload' => $request->all()]);
    }

    public function destroy(Page $page): JsonResponse
    {
        return response()->json(['ok' => true, 'id' => $page->id]);
    }
}

