<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VertexCMS')</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="vc-admin-body @yield('body_class')">
    @php
        $user = auth()->user();
        $navigation = [
            [
                'section' => __('admin.nav.workspace') ?? 'Workspace',
                'items' => [
                    ['label' => __('admin.dashboard'), 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'permission' => 'admin.access', 'description' => __('admin.nav.dashboard_desc') ?? 'Сводка и активность', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />'],
                    ['label' => __('admin.pages'), 'route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'permission' => 'pages.view', 'description' => __('admin.nav.pages_desc') ?? 'Контент и публикации', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />'],
                    ['label' => __('admin.media'), 'route' => 'admin.media.index', 'active' => 'admin.media.*', 'permission' => 'media.view', 'description' => __('admin.nav.media_desc') ?? 'Файлы и изображения', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z" />'],
                ],
            ],
            [
                'section' => __('admin.nav.management') ?? 'Management',
                'items' => [
                    ['label' => __('admin.users'), 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'permission' => 'users.view', 'description' => __('admin.nav.users_desc') ?? 'Аккаунты и доступ', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0z" />'],
                    ['label' => __('admin.roles'), 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'permission' => 'roles.view', 'description' => __('admin.nav.roles_desc') ?? 'Наборы разрешений', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />'],
                    ['label' => __('admin.taxonomies') ?? 'Таксономии', 'route' => 'admin.taxonomies.index', 'active' => 'admin.taxonomies.*', 'permission' => 'taxonomy.view', 'description' => __('admin.nav.taxonomies_desc') ?? 'Рубрики, теги и архивы', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 6h9m-9 4.5h9m-9 4.5h5.25M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z" />'],
                    ['label' => __('admin.settings'), 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'permission' => 'settings.view', 'description' => __('admin.nav.settings_desc') ?? 'Сайт, API, AI и PWA', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />'],
                ],
            ],
            [
                'section' => __('admin.nav.operations') ?? 'Operations',
                'items' => [
                    ['label' => __('admin.system'), 'route' => 'admin.system.info', 'active' => 'admin.system.info', 'permission' => 'system.view', 'description' => __('admin.nav.system_desc') ?? 'Среда и модули', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83m0 0a2.968 2.968 0 0 1 0-4.183L15.75 6m-2.25 8.25a2.968 2.968 0 0 1-4.183 0L3.75 8.25" />'],
                    ['label' => __('admin.nav.security') ?? 'Безопасность', 'route' => 'admin.system.security', 'active' => 'admin.system.security*', 'permission' => 'system.view', 'description' => __('admin.nav.security_desc') ?? 'Core, integrity и toggle-модули', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7.5 3v5.25c0 4.706-3.068 8.854-7.5 10.25-4.432-1.396-7.5-5.544-7.5-10.25V6L12 3Zm0 5.25v5.25l3 3" />'],
                    ['label' => __('admin.nav.analytics') ?? 'Аналитика', 'route' => 'admin.system.analytics', 'active' => 'admin.system.analytics', 'permission' => 'analytics.view', 'description' => __('admin.nav.analytics_desc') ?? 'Трафик и аудитория', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125h3.75v7.125H3v-7.125Zm7.125-9.375h3.75V20.25h-3.75V3.75Zm7.125 5.625H21V20.25h-3.75V9.375Z" />'],
                    ['label' => __('admin.cache'), 'route' => 'admin.system.cache', 'active' => 'admin.system.cache*', 'permission' => 'system.view', 'description' => __('admin.nav.cache_desc') ?? 'Состояние и очистка', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />'],
                    ['label' => __('admin.logs'), 'route' => 'admin.system.logs', 'active' => 'admin.system.logs', 'permission' => 'system.view', 'description' => __('admin.nav.logs_desc') ?? 'Аудит действий', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3z" />'],
                ],
            ],
        ];

        if (\Illuminate\Support\Facades\Route::has('admin.forms.index')) {
            $navigation[0]['items'][] = [
                'label' => __('admin.nav.forms') ?? 'Формы',
                'route' => 'admin.forms.index',
                'active' => 'admin.forms.*',
                'permission' => 'forms.view',
                'description' => __('admin.nav.forms_desc') ?? 'Конструктор форм и калькуляторов',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75h6A2.25 2.25 0 0 1 17.25 6v12A2.25 2.25 0 0 1 15 20.25H9A2.25 2.25 0 0 1 6.75 18V6A2.25 2.25 0 0 1 9 3.75Zm-3 3h12M9.75 10.5h4.5m-4.5 3h4.5m-4.5 3h2.25" />',
            ];
        }

        if (\Illuminate\Support\Facades\Route::has('admin.seo.dashboard')) {
            $navigation[1]['items'][] = [
                'label' => 'SEO',
                'route' => 'admin.seo.dashboard',
                'active' => 'admin.seo.*',
                'permission' => 'seo.view',
                'description' => __('admin.nav.seo_desc') ?? 'Метаданные, sitemap и редиректы',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9m-9 6h9m-9 6h9M4.5 6h.008v.008H4.5V6Zm0 6h.008v.008H4.5V12Zm0 6h.008v.008H4.5V18Z" />',
            ];
        }

        $segments = request()->segments();
        $translations = [
            'admin' => __('admin.dashboard'),
            'pages' => __('admin.pages'),
            'media' => __('admin.media'),
            'users' => __('admin.users'),
            'roles' => __('admin.roles'),
            'settings' => __('admin.settings'),
            'taxonomies' => __('admin.taxonomies') ?? 'Taxonomies',
            'system' => __('admin.system'),
            'cache' => __('admin.cache'),
            'logs' => __('admin.logs'),
            'create' => __('admin.create') ?? 'Create',
            'edit' => __('admin.edit') ?? 'Edit',
        ];
    @endphp

    <div id="sidebar-backdrop" data-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>

    <div class="vc-admin-shell lg:flex">
        <aside id="sidebar" class="vc-admin-sidebar fixed inset-y-0 left-0 z-50 w-80 transform overflow-y-auto border-r -translate-x-full transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:z-0 lg:min-h-screen">
            <div class="px-6 pb-6 pt-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="vc-brand-mark">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7.5 12 3l8 4.5M4 7.5V16.5L12 21m-8-13.5 8 4.5m8-4.5V16.5L12 21m0-9v9" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">VertexCMS</p>
                            <p class="mt-1 text-lg font-semibold text-slate-50">{{ config_value('site.name', config('app.name')) }}</p>
                            <p class="text-sm text-slate-400">Content workspace</p>
                        </div>
                    </div>
                    <button data-sidebar-toggle class="rounded-xl p-2 text-slate-400 transition hover:bg-white/10 hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 px-4 py-4 text-sm text-slate-300">
                    <p class="font-semibold text-slate-100">{{ $user?->name ?? __('admin.administrator') }}</p>
                    <p class="mt-1 text-slate-400">{{ $user?->email }}</p>
                </div>
            </div>

            <nav class="px-4 pb-8">
                @foreach ($navigation as $group)
                    @php
                        $visibleItems = collect($group['items'])
                            ->filter(fn ($item) => ! $item['permission'] || $user?->hasPermission($item['permission']));
                    @endphp

                    @continue($visibleItems->isEmpty())

                    <section class="mb-7">
                        <p class="vc-nav-section-title">{{ $group['section'] }}</p>
                        <div class="space-y-1.5">
                            @foreach ($visibleItems as $item)
                                @php
                                    $isActive = request()->routeIs($item['active']);
                                @endphp
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="vc-nav-link {{ $isActive ? 'vc-nav-link-active' : '' }}"
                                >
                                    <span class="vc-nav-icon">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            {!! $item['icon'] !!}
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold">{{ $item['label'] }}</span>
                                        <span class="block truncate text-xs text-slate-400">{{ $item['description'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </nav>
        </aside>

        <div class="vc-main-column flex flex-col">
            <header class="vc-topbar sticky top-0 z-30">
                <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-4 py-4 sm:px-6">
                    <div class="flex items-center gap-4">
                        <button data-sidebar-toggle class="rounded-xl p-2 text-[var(--vc-text-muted)] transition hover:bg-black/5 lg:hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-semibold tracking-tight text-[var(--vc-text)] sm:text-2xl">@yield('page_title', __('admin.dashboard'))</h1>
                            @hasSection('page_subtitle')
                                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">@yield('page_subtitle')</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-2 border-r border-[var(--vc-border)] pr-3 mr-1">
                            <a href="{{ route('admin.locale.change', 'ru') }}" class="text-xs font-bold px-2 py-1 rounded {{ app()->getLocale() === 'ru' ? 'bg-[var(--vc-accent)] text-white' : 'text-[var(--vc-text-muted)] hover:bg-black/5' }}">RU</a>
                            <a href="{{ route('admin.locale.change', 'en') }}" class="text-xs font-bold px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-[var(--vc-accent)] text-white' : 'text-[var(--vc-text-muted)] hover:bg-black/5' }}">EN</a>
                        </div>

                        <button type="button" data-theme-toggle class="vc-button vc-button-secondary vc-theme-toggle px-3 py-2">
                            <svg data-theme-icon class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                            <span data-theme-label class="hidden sm:inline">{{ __('admin.toggle_theme') ?? 'Theme' }}</span>
                        </button>

                        <a href="{{ url('/') }}" target="_blank" class="vc-button vc-button-secondary hidden px-3 py-2 sm:inline-flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <span>{{ __('admin.visit_site') ?? 'Перейти на сайт' }}</span>
                        </a>

                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="vc-button vc-button-secondary px-3 py-2">
                                {{ __('admin.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="vc-page-wrap @yield('page_wrap_class')">
                <nav class="mb-6 flex overflow-x-auto whitespace-nowrap" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="vc-breadcrumb-link inline-flex items-center text-xs font-medium">
                                <svg class="w-3.5 h-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                                VertexCMS
                            </a>
                        </li>
                        @php $currentUrl = ''; @endphp
                        @foreach($segments as $segment)
                            @if($segment !== 'admin')
                                @php $currentUrl .= '/' . $segment; @endphp
                                <li>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-[var(--vc-text-soft)]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                        <a href="{{ url('admin' . $currentUrl) }}" class="vc-breadcrumb-link ml-1 text-xs font-medium md:ml-2">
                                            {{ $translations[$segment] ?? ucfirst($segment) }}
                                        </a>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>

                @if (session('status'))
                    <div class="vc-status-flash mb-6 rounded-2xl px-4 py-3 text-sm font-medium shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
