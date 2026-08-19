<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\System\Services\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = EmailLog::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('template_key')) {
            $query->where('template_key', $request->input('template_key'));
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('recipient_email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }

        $logs = $query->paginate($request->integer('per_page', 50));

        $templates = EmailLog::query()->selectRaw('template_key, COUNT(*) as count')
            ->groupBy('template_key')
            ->pluck('count', 'template_key')
            ->all();

        return view('admin.email-logs.index', [
            'logs' => $logs,
            'templates' => $templates,
            'statuses' => [
                'pending' => 'В очереди',
                'sent' => 'Отправлено',
                'failed' => 'Ошибка',
                'bounced' => 'Возвращено',
            ],
        ]);
    }

    public function show(EmailLog $log): View
    {
        return view('admin.email-logs.show', [
            'log' => $log,
        ]);
    }

    public function destroy(EmailLog $log): RedirectResponse
    {
        $log->delete();

        return redirect()
            ->route('admin.email-logs.index')
            ->with('status', 'Лог удалён.');
    }

    public function resend(Request $request, EmailLog $log): RedirectResponse
    {
        if ($log->status === 'sent') {
            return redirect()
                ->back()
                ->withErrors(['resend' => 'Письмо уже было отправлено.']);
        }

        $template = \App\Models\EmailTemplate::query()->where('key', $log->template_key)->firstOrFail();

        try {
            $this->emailService->send(
                $log->template_key,
                $log->recipient_email,
                $log->recipient_name,
                $log->template_vars ?? []
            );

            return redirect()
                ->back()
                ->with('status', 'Письмо поставлено в очередь на повторную отправку.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['resend' => 'Ошибка: '.$e->getMessage()]);
        }
    }
}
