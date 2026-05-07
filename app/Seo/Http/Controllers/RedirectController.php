<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Redirect::query()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'payload' => $request->all()], 201);
    }

    public function update(Request $request, Redirect $redirect): JsonResponse
    {
        return response()->json(['ok' => true, 'id' => $redirect->id, 'payload' => $request->all()]);
    }

    public function destroy(Redirect $redirect): JsonResponse
    {
        return response()->json(['ok' => true, 'id' => $redirect->id]);
    }
}

