<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $query = Redirect::query()->latest();

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('from_url', 'like', '%'.$search.'%')
                    ->orWhere('to_url', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', (int) $request->input('status_code'));
        }

        if ($request->filled('enabled')) {
            $query->where('enabled', $request->input('enabled') === '1');
        }

        $redirects = $query->paginate(25)->withQueryString();

        if (! $request->wantsJson() && ! $request->expectsJson()) {
            $allRedirects = Redirect::query()->get();

            return view('admin.seo.redirects.index', [
                'redirects' => $redirects,
                'defaultStatusCodes' => [301, 302, 307, 308],
                'stats' => [
                    'total' => $allRedirects->count(),
                    'enabled' => $allRedirects->where('enabled', true)->count(),
                    'top_hits' => (int) $allRedirects->max('hits'),
                ],
                'error404Count' => 0,
                'recent404s' => [],
            ]);
        }

        return response()->json($redirects);
    }

    /**
     * Логи 404 ошибок
     */
    public function logs(): View
    {
        $logs = \App\Models\RedirectLog::where('status_code', 404)
            ->latest()
            ->paginate(50);

        return view('admin.seo.redirects.logs', compact('logs'));
    }

    /**
     * Импорт 404 ошибок в редиректы
     */
    public function importFromLogs(Request $request): \Illuminate\Http\RedirectResponse
    {
        $limit = $request->input('limit', 50);
        
        $errors = \App\Models\RedirectLog::where('status_code', 404)
            ->latest()
            ->take($limit)
            ->get();

        $created = 0;
        foreach ($errors as $error) {
            \App\Models\SeoRedirect::create([
                'from_url' => $this->normalizeUrl($error->url),
                'to_url' => '/',
                'type' => 301,
                'is_active' => true,
            ]);
            $created++;
        }

        return redirect()->back()->with('success', "Создано {$created} редиректов из логов 404!");
    }

    /**
     * Массовый импорт из CSV
     */
    public function bulkImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        
        $created = 0;
        $skipped = 0;
        
        foreach ($rows as $index => $row) {
            if ($index === 0 || count($row) < 2) continue;
            
            $fromUrl = trim($row[0]);
            $toUrl = trim($row[1]);
            $type = isset($row[2]) ? (int)trim($row[2]) : 301;
            
            if (empty($fromUrl) || empty($toUrl)) {
                $skipped++;
                continue;
            }
            
            if (\App\Models\SeoRedirect::where('from_url', $this->normalizeUrl($fromUrl))->exists()) {
                $skipped++;
                continue;
            }
            
            \App\Models\SeoRedirect::create([
                'from_url' => $this->normalizeUrl($fromUrl),
                'to_url' => $this->normalizeUrl($toUrl),
                'type' => in_array($type, [301, 302]) ? $type : 301,
                'is_active' => true,
            ]);
            $created++;
        }

        return redirect()->back()->with('success', "Импортировано {$created} редиректов. Пропущено: {$skipped}");
    }

    private function normalizeUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '' || str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return '/'.ltrim($value, '/');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $payload = $request->validate([
            'from_url' => ['required', 'string', 'max:500', 'unique:redirects,from_url'],
            'to_url' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'integer', 'in:301,302,307,308'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $redirect = Redirect::query()->create([
            ...$this->normalizePayload($payload),
            'enabled' => $request->boolean('enabled', true),
            'hits' => 0,
        ]);

        if (! $request->wantsJson() && ! $request->expectsJson()) {
            return redirect()
                ->route('admin.redirects.index')
                ->with('status', 'Правило перенаправления создано.');
        }

        return response()->json($redirect, 201);
    }

    public function update(Request $request, Redirect $redirect): JsonResponse|RedirectResponse
    {
        $payload = $request->validate([
            'from_url' => ['sometimes', 'required', 'string', 'max:500', 'unique:redirects,from_url,'.$redirect->id],
            'to_url' => ['sometimes', 'required', 'string', 'max:500'],
            'status_code' => ['sometimes', 'required', 'integer', 'in:301,302,307,308'],
            'enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('is_active', $payload) && ! array_key_exists('enabled', $payload)) {
            $payload['enabled'] = (bool) $payload['is_active'];
        }

        unset($payload['is_active']);

        if (! $request->wantsJson() && ! $request->expectsJson()) {
            $payload['enabled'] = $request->boolean('enabled');
        }

        $redirect->update($this->normalizePayload($payload));

        if (! $request->wantsJson() && ! $request->expectsJson()) {
            return redirect()
                ->route('admin.redirects.index')
                ->with('status', 'Правило перенаправления обновлено.');
        }

        return response()->json($redirect);
    }

    public function destroy(Request $request, Redirect $redirect): JsonResponse|RedirectResponse
    {
        $redirect->delete();

        if (! $request->wantsJson() && ! $request->expectsJson()) {
            return redirect()
                ->route('admin.redirects.index')
                ->with('status', 'Правило перенаправления удалено.');
        }

        return response()->json(['ok' => true, 'id' => $redirect->id]);
    }

    public function show(Redirect $redirect): JsonResponse
    {
        return response()->json($redirect);
    }

    private function normalizePayload(array $payload): array
    {
        $payload = Arr::only($payload, ['from_url', 'to_url', 'status_code', 'enabled']);

        if (array_key_exists('from_url', $payload)) {
            $payload['from_url'] = $this->normalizeUrlValue($payload['from_url']);
        }

        if (array_key_exists('to_url', $payload)) {
            $payload['to_url'] = $this->normalizeUrlValue($payload['to_url']);
        }

        return $payload;
    }

    private function normalizeUrlValue(string $value): string
    {
        $value = trim($value);

        if ($value === '' || str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return '/'.ltrim($value, '/');
    }
}
