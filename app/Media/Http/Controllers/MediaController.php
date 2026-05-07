<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        return view('admin.media.index', [
            'items' => Media::query()->latest()->paginate(24),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.media.index');
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        return redirect()->route('admin.media.index');
    }

    public function destroy(Media $media): RedirectResponse
    {
        return redirect()->route('admin.media.index');
    }
}

