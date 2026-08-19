<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\System\Services\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailTemplateController extends Controller
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {
    }

    public function index(): View
    {
        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::query()->orderBy('category')->orderBy('name')->get(),
            'categories' => EmailTemplate::query()->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.email-templates.edit', [
            'template' => null,
            'defaultVars' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateTemplate($request);

        $data = $request->only(['key', 'name', 'subject', 'body_html', 'body_text', 'default_vars', 'category', 'is_active']);
        $data['default_vars'] = $this->parseJsonVar($request->input('default_vars'));
        $data['is_system'] = false;

        EmailTemplate::query()->create($data);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('status', 'Шаблон сохранён.');
    }

    public function edit(EmailTemplate $template): View
    {
        abort_if($template->is_system, 403, 'Системные шаблоны нельзя редактировать.');

        return view('admin.email-templates.edit', [
            'template' => $template,
            'defaultVars' => $template->default_vars ?? [],
        ]);
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        abort_if($template->is_system, 403, 'Системные шаблоны нельзя редактировать.');

        $this->validateTemplate($request);

        $data = $request->only(['key', 'name', 'subject', 'body_html', 'body_text', 'default_vars', 'category', 'is_active']);
        $data['default_vars'] = $this->parseJsonVar($request->input('default_vars'));

        $template->forceFill($data)->save();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('status', 'Шаблон обновлён.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        abort_if($template->is_system, 403, 'Системные шаблоны нельзя удалить.');

        $template->delete();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('status', 'Шаблон удалён.');
    }

    public function sendTest(Request $request, EmailTemplate $template): RedirectResponse
    {
        $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $this->emailService->send(
                $template->key,
                $request->input('test_email'),
                'Test User',
                $template->default_vars ?? []
            );

            return redirect()
                ->back()
                ->with('status', 'Тестовое письмо отправлено.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['test_email' => 'Ошибка отправки: '.$e->getMessage()]);
        }
    }

    public function preview(EmailTemplate $template): View
    {
        $rendered = $this->emailService->renderBody($template->body_html, $template->default_vars ?? []);

        return view('admin.email-templates.preview', [
            'template' => $template,
            'html' => $rendered,
        ]);
    }

    private function validateTemplate(Request $request): void
    {
        $validator = \Validator::make($request->all(), [
            'key' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:email_templates,key'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'body_html' => ['required', 'string'],
            'body_text' => ['nullable', 'string'],
            'default_vars' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Unique check for update
        if ($request->isMethod('put')) {
            $validator->sometimes('key', 'unique:email_templates,key,'.$request->route('template')->id, function () {
                return true;
            });
        }

        $validator->validate();
    }

    private function parseJsonVar(?string $json): array
    {
        if (!$json) return [];
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
