<?php

namespace App\System\Http\Controllers;

use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Theme\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PwaController extends Controller
{
    public function __construct(
        private readonly ThemeManager $themes,
        private readonly PageRenderer $renderer,
    ) {
    }

    public function offline(): View
    {
        abort_unless((bool) config_value('pwa.enabled', false), 404);

        $page = null;
        $html = '';
        $offlinePageId = (int) config_value('pwa.offline_page_id', 0);

        if ($offlinePageId > 0) {
            $page = Page::query()
                ->with('seoMeta.ogImage')
                ->whereKey($offlinePageId)
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->first();

            $html = (string) $this->renderer->render($page?->content_json);
        }

        return view($this->themes->offlineView(), [
            'page' => $page,
            'html' => $html,
        ]);
    }

    public function serviceWorker(Request $request): Response
    {
        abort_unless((bool) config_value('pwa.enabled', false), 404);

        $cacheName = 'vertexcms-shell-'.md5((string) config('vertex.version', '0.1.0'));
        $offlineUrl = route('frontend.offline');
        $manifestUrl = route('frontend.manifest');
        $startUrl = config_value('pwa.start_url', '/');

        $script = <<<JS
const CACHE_NAME = '{$cacheName}';
const OFFLINE_URL = '{$offlineUrl}';
const PRECACHE_URLS = ['{$startUrl}', '{$manifestUrl}', OFFLINE_URL];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys
      .filter((key) => key !== CACHE_NAME)
      .map((key) => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        return response;
      })
      .catch(() => caches.match(event.request).then((cached) => cached || caches.match(OFFLINE_URL)))
  );
});
JS;

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
