<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VertexCMS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    @php
        $user = auth()->user();
        $navigation = [
            ['label' => 'Панель управления', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'permission' => 'admin.access'],
            ['label' => 'Страницы', 'route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'permission' => 'pages.view'],
            ['label' => 'Медиа', 'route' => 'admin.media.index', 'active' => 'admin.media.*', 'permission' => 'media.view'],
            ['label' => 'Пользователи', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'permission' => 'users.view'],
            ['label' => 'Роли', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'permission' => 'roles.view'],
            ['label' => 'Настройки', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'permission' => 'settings.view'],
            ['label' => 'Система', 'route' => 'admin.system.info', 'active' => 'admin.system.info', 'permission' => 'system.view'],
            ['label' => 'Кеш', 'route' => 'admin.system.cache', 'active' => 'admin.system.cache*', 'permission' => 'system.view'],
            ['label' => 'Логи', 'route' => 'admin.system.logs', 'active' => 'admin.system.logs', 'permission' => 'system.view'],
        ];
    @endphp

    <div class="min-h-screen lg:flex">
        <aside class="border-b border-slate-200 bg-white lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r">
            <div class="px-6 py-5">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
                <p class="mt-1 text-lg font-semibold">{{ config_value('site.name', config('app.name')) }}</p>
            </div>

            <nav class="flex gap-2 overflow-x-auto px-4 pb-4 lg:block lg:space-y-1 lg:overflow-visible">
                @foreach ($navigation as $item)
                    @continue($item['permission'] && ! $user?->hasPermission($item['permission']))
                    <a
                        href="{{ route($item['route']) }}"
                        class="block whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['active']) ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="border-b border-slate-200 bg-white">
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <h1 class="text-xl font-semibold">@yield('page_title', 'Панель управления')</h1>
                        @hasSection('page_subtitle')
                            <p class="mt-1 text-sm text-slate-500">@yield('page_subtitle')</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">
                            Выйти
                        </button>
                    </form>
                </div>
            </header>

            <main class="px-6 py-8">
                @if (session('status'))
                    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
