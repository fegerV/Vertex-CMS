<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RedirectController extends Controller
{
    public function index(): JsonResponse
    {
        $redirects = Redirect::query()->with('creator')->latest()->paginate(50);
        return response()->json($redirects);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'from_url' => ['required', 'string', 'max:500', 'unique:redirects,from_url'],
            'to_url' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'integer', 'in:301,302,307,308'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $redirect = Redirect::query()->create([
            ...$payload,
            'created_by' => $request->user()->id ?? 1,
        ]);

        return response()->json($redirect, 201);
    }

    public function update(Request $request, Redirect $redirect): JsonResponse
    {
        $payload = $request->validate([
            'to_url' => ['sometimes', 'required', 'string', 'max:500'],
            'status_code' => ['sometimes', 'required', 'integer', 'in:301,302,307,308'],
            'comment' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $redirect->update($payload);

        return response()->json($redirect);
    }

    public function destroy(Redirect $redirect): JsonResponse
    {
        $redirect->delete();
        return response()->json(['ok' => true, 'id' => $redirect->id]);
    }

    public function show(Redirect $redirect): JsonResponse
    {
        return response()->json($redirect);
    }
}

