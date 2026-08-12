<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\Webhooks\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function __construct(private readonly WebhookService $webhooks) {}

    public function index(): View
    {
        return view('admin.integrations.webhooks', [
            'webhooks' => Webhook::query()->with(['logs' => fn ($query) => $query->latest()->limit(5)])->latest()->get(),
            'events' => $this->webhooks->getAvailableEvents(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->webhooks->createWebhook($this->validated($request));

        return back()->with('success', 'Webhook created.');
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $payload = $this->validated($request);
        $this->webhooks->validateUrl($payload['url']);
        $webhook->update($payload);

        return back()->with('success', 'Webhook updated.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $webhook->delete();

        return back()->with('success', 'Webhook deleted.');
    }

    public function test(Webhook $webhook): RedirectResponse
    {
        $this->webhooks->triggerWebhookFor($webhook, 'webhook.test', [
            'message' => 'Test delivery from VertexCMS',
            'initiated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Test delivery queued.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:https', 'max:2048'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:'.implode(',', array_keys($this->webhooks->getAvailableEvents()))],
            'headers' => ['nullable', 'array'],
            'headers.*' => ['nullable', 'string', 'max:1000'],
            'retry_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
