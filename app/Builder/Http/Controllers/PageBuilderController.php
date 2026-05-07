<?php

namespace App\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function edit(Page $page): View
    {
        return view('admin.builder.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        return redirect()->route('admin.pages.builder', $page);
    }
}

