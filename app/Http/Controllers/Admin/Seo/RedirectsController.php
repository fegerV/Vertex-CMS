<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\RedirectLog;
use App\Models\SeoRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedirectsController extends Controller
{
    /**
     * Отображение списка редиректов и 404 ошибок
     */
    public function index()
    {
        $redirects = SeoRedirect::latest()->paginate(20);
        $error404Count = RedirectLog::where('status_code', 404)->count();
        $recent404s = RedirectLog::where('status_code', 404)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.seo.redirects.index', compact('redirects', 'error404Count', 'recent404s'));
    }

    /**
     * Логи 404 ошибок
     */
    public function logs()
    {
        $logs = RedirectLog::where('status_code', 404)
            ->latest()
            ->paginate(50);

        return view('admin.seo.redirects.logs', compact('logs'));
    }

    /**
     * Создание нового редиректа
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_url' => 'required|string|max:255|unique:seo_redirects,from_url',
            'to_url' => 'required|string|max:255',
            'type' => 'required|in:301,302',
        ]);

        SeoRedirect::create([
            'from_url' => $this->normalizeUrl($request->from_url),
            'to_url' => $this->normalizeUrl($request->to_url),
            'type' => $request->type,
            'is_active' => true,
            'note' => $request->note ?? null,
        ]);

        return redirect()->back()->with('success', 'Редирект успешно создан!');
    }

    /**
     * Обновление редиректа
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'from_url' => 'required|string|max:255|unique:seo_redirects,from_url,'.$id,
            'to_url' => 'required|string|max:255',
            'type' => 'required|in:301,302',
            'is_active' => 'boolean',
        ]);

        $redirect = SeoRedirect::findOrFail($id);
        $redirect->update([
            'from_url' => $this->normalizeUrl($request->from_url),
            'to_url' => $this->normalizeUrl($request->to_url),
            'type' => $request->type,
            'is_active' => $request->has('is_active'),
            'note' => $request->note ?? null,
        ]);

        return redirect()->back()->with('success', 'Редирект обновлен!');
    }

    /**
     * Удаление редиректа
     */
    public function destroy($id)
    {
        $redirect = SeoRedirect::findOrFail($id);
        $redirect->delete();

        return redirect()->back()->with('success', 'Редирект удален!');
    }

    /**
     * Импорт 404 ошибок в редиректы
     */
    public function importFromLogs(Request $request)
    {
        $limit = $request->input('limit', 50);
        
        $errors = RedirectLog::where('status_code', 404)
            ->whereDoesntHave('redirect')
            ->latest()
            ->take($limit)
            ->get();

        $created = 0;
        foreach ($errors as $error) {
            SeoRedirect::create([
                'from_url' => $this->normalizeUrl($error->url),
                'to_url' => '/', // По умолчанию на главную, можно изменить
                'type' => 301,
                'is_active' => true,
            ]);
            $created++;
        }

        return redirect()->back()->with('success', "Создано {$created} редиректов из логов 404!");
    }

    /**
     * Массовое создание редиректов из CSV
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        
        $created = 0;
        $skipped = 0;
        
        foreach ($rows as $index => $row) {
            if ($index === 0 || count($row) < 2) continue; // Пропускаем заголовок
            
            $fromUrl = trim($row[0]);
            $toUrl = trim($row[1]);
            $type = isset($row[2]) ? (int)trim($row[2]) : 301;
            
            if (empty($fromUrl) || empty($toUrl)) {
                $skipped++;
                continue;
            }
            
            // Проверяем, существует ли уже такой редирект
            if (SeoRedirect::where('from_url', $this->normalizeUrl($fromUrl))->exists()) {
                $skipped++;
                continue;
            }
            
            SeoRedirect::create([
                'from_url' => $this->normalizeUrl($fromUrl),
                'to_url' => $this->normalizeUrl($toUrl),
                'type' => in_array($type, [301, 302]) ? $type : 301,
                'is_active' => true,
            ]);
            $created++;
        }

        return redirect()->back()->with('success', "Импортировано {$created} редиректов. Пропущено: {$skipped}");
    }

    /**
     * Нормализация URL
     */
    private function normalizeUrl($url)
    {
        $url = trim($url);
        if (!str_starts_with($url, '/')) {
            $url = '/' . ltrim($url, 'http://' . config('app.url') . '/');
        }
        return '/' . trim($url, '/');
    }
}
