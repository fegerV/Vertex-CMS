<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GdprSetting;
use App\Models\IpFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function gdpr(): View
    {
        $settings = GdprSetting::first() ?? new GdprSetting();
        return view('admin.security.gdpr', compact('settings'));
    }

    public function updateGdpr(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'banner_title' => 'required|string|max:255',
            'banner_message' => 'required|string',
            'accept_button_text' => 'required|string|max:100',
            'decline_button_text' => 'required|string|max:100',
            'policy_link' => 'nullable|url',
            'cookie_duration_days' => 'required|integer|min:1|max:730',
        ]);

        $settings = GdprSetting::firstOrCreate([]);
        $settings->update($validated);

        return redirect()->route('admin.security.gdpr')
            ->with('status', 'Настройки GDPR обновлены.');
    }

    public function ipFilters(Request $request): View
    {
        $filters = IpFilter::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('active') !== null, fn ($q) => $q->where('active', $request->boolean('active')))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.security.ip-filters', compact('filters'));
    }

    public function storeIpFilter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:100',
            'type' => 'required|in:blacklist,whitelist',
            'reason' => 'nullable|string|max:500',
            'active' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        IpFilter::create([
            'ip_address' => $validated['ip_address'],
            'type' => $validated['type'],
            'reason' => $validated['reason'] ?? null,
            'active' => $validated['active'] ?? true,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.security.ip-filters')
            ->with('status', 'IP-фильтр добавлен.');
    }

    public function updateIpFilter(Request $request, IpFilter $ipFilter): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:100',
            'type' => 'required|in:blacklist,whitelist',
            'reason' => 'nullable|string|max:500',
            'active' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $ipFilter->update($validated);

        return redirect()->route('admin.security.ip-filters')
            ->with('status', 'IP-фильтр обновлён.');
    }

    public function destroyIpFilter(IpFilter $ipFilter): RedirectResponse
    {
        $ipFilter->delete();

        return redirect()->route('admin.security.ip-filters')
            ->with('status', 'IP-фильтр удалён.');
    }
}
