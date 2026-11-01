<?php

namespace App\Http\Controllers;

use App\Models\WebVitalMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Receives Core Web Vitals beacons from public pages.
 *
 * This endpoint is intentionally unauthenticated: the metrics come from
 * anonymous visitors. It lives on the API middleware group (no session, no
 * CSRF token) and is throttled, so a page can report its measurements with
 * navigator.sendBeacon().
 */
class WebVitalController extends Controller
{
    public const METRICS = ['LCP', 'FID', 'CLS', 'INP', 'TTFB'];

    /**
     * Metadata keys the tracker may send. Anything else is dropped so the
     * public endpoint cannot be used to store arbitrary payloads.
     */
    public const METADATA_KEYS = [
        'userAgent',
        'language',
        'screenResolution',
        'viewportSize',
        'connectionType',
        'deviceMemory',
        'hardwareConcurrency',
    ];

    public function store(Request $request): JsonResponse
    {
        abort_unless((bool) config_value('web_vitals.enabled', true), 404);

        $validated = $request->validate([
            'metric_type' => ['required', 'string', Rule::in(self::METRICS)],
            'value' => ['required', 'numeric', 'min:0', 'max:3600'],
            'url' => ['nullable', 'string', 'max:2048'],
            'session_id' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array', 'max:10'],
        ]);

        $value = (float) $validated['value'];
        $metadata = Arr::only($validated['metadata'] ?? [], self::METADATA_KEYS);

        $metric = WebVitalMetric::query()->create([
            'session_id' => $validated['session_id'] ?? null,
            'url' => $this->normalizeUrl($validated['url'] ?? null),
            'metric_type' => $validated['metric_type'],
            'value' => $value,
            'rating' => WebVitalMetric::getRating($validated['metric_type'], $value),
            'metadata' => $metadata !== [] ? $metadata : null,
            'measured_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'rating' => $metric->rating,
        ], 201);
    }

    /**
     * Strip the query string and fragment before storing a page URL: they can
     * carry search terms or personal data, and they are not needed to group
     * measurements per page.
     */
    private function normalizeUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $parts = parse_url(trim($url));

        if ($parts === false || ! isset($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '/';

        return $scheme.'://'.$parts['host'].$port.$path;
    }
}
