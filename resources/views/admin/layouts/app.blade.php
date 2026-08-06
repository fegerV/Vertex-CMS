<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VertexCMS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Smooth scrolling */
        html { scroll-behavior: smooth; }
        
        /* Focus visible for better accessibility */
        :focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Loading animation */
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .loading { animation: pulse-subtle 1.5s ease-in-out infinite; }
        
        /* Sidebar collapsed state */
        .sidebar-collapsed .sidebar-logo-text,
        .sidebar-collapsed .sidebar-link-text,
        .sidebar-collapsed .sidebar-user-info {
            display: none;
        }
        .sidebar-collapsed .sidebar-nav-item {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .sidebar-collapsed .sidebar-nav-icon {
            margin-right: 0;
        }
        .sidebar-collapsed {
            width: 4.5rem !important;
        }
        .sidebar-collapsed .sidebar-header {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased dark:bg-slate-900 dark:text-slate-100" x-data="{ sidebarOpen: false, sidebarCollapsed: false, searchOpen: false, userMenuOpen: false }" :class="{ 'sidebar-collapsed': sidebarCollapsed && !sidebarOpen }">
    @php
        $user = auth()->user();
        $navigation = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'permission' => 'admin.access', 'icon' => 'home'],
            ['label' => 'Страницы', 'route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'permission' => 'pages.view', 'icon' => 'document'],
            ['label' => 'Медиа', 'route' => 'admin.media.index', 'active' => 'admin.media.*', 'permission' => 'media.view', 'icon' => 'image'],
            ['label' => 'Формы', 'route' => 'admin.forms.index', 'active' => 'admin.forms.*', 'permission' => 'forms.view', 'icon' => 'clipboard'],
            [
                'label' => 'Магазин',
                'route' => 'admin.ecommerce.products.index',
                'active' => 'admin.ecommerce.*',
                'permission' => 'ecommerce.products.view',
                'icon' => 'shopping-cart',
                'children' => [
                    ['label' => 'Товары', 'route' => 'admin.ecommerce.products.index', 'permission' => 'ecommerce.products.view'],
                    ['label' => 'Заказы', 'route' => 'admin.ecommerce.orders.index', 'permission' => 'ecommerce.orders.view'],
                    ['label' => 'Настройки', 'route' => 'admin.ecommerce.settings', 'permission' => 'ecommerce.settings.view'],
                    ['label' => 'Уведомления', 'route' => 'admin.ecommerce.notifications', 'permission' => 'ecommerce.notifications.view'],
                ]
            ],
            [
                'label' => 'SEO',
                'route' => 'admin.seo.dashboard',
                'active' => 'admin.seo.*',
                'permission' => 'seo.view',
                'icon' => 'search',
                'children' => [
                    ['label' => 'Обзор', 'route' => 'admin.seo.dashboard', 'permission' => 'seo.view'],
                    ['label' => 'Анализ контента', 'route' => 'admin.seo.analysis', 'permission' => 'seo.view'],
                    ['label' => 'Массовое редактирование', 'route' => 'admin.seo.bulk-editor', 'permission' => 'seo.edit'],
                    ['label' => 'Роботы и Файлы', 'route' => 'admin.seo.files', 'permission' => 'seo.edit'],
                    ['label' => 'Семантическое ядро', 'route' => 'admin.seo.semantics', 'permission' => 'seo.view'],
                    ['label' => 'Внутренние ссылки', 'route' => 'admin.seo.internal-links', 'permission' => 'seo.view'],
                    ['label' => 'AI-Ассистент', 'route' => 'admin.seo.ai-assistant', 'permission' => 'seo.edit'],
                    ['label' => '404 Monitor', 'route' => 'admin.seo.redirects', 'permission' => 'seo.edit'],
                    ['label' => 'Schema.org', 'route' => 'admin.seo.schema-builder', 'permission' => 'seo.edit'],
                    ['label' => 'Search Console', 'route' => 'admin.seo.search-console', 'permission' => 'seo.view'],
                    ['label' => 'Дубликаты', 'route' => 'admin.seo.duplicates', 'permission' => 'seo.view'],
                    ['label' => 'AI Изображения', 'route' => 'admin.seo.ai-images', 'permission' => 'seo.edit'],
                    ['label' => 'Анализатор Изображений', 'route' => 'admin.seo.images.index', 'permission' => 'seo.edit'],
                    ['label' => 'Соцсети (OG)', 'route' => 'admin.seo.social-media', 'permission' => 'seo.edit'],
                    ['label' => 'Настройки SEO', 'route' => 'admin.seo.settings', 'permission' => 'seo.settings'],
                ]
            ],
            ['label' => 'Пользователи', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'permission' => 'users.view', 'icon' => 'users'],
            ['label' => 'Роли', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'permission' => 'roles.view', 'icon' => 'shield'],
            ['label' => 'Настройки', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'permission' => 'settings.view', 'icon' => 'cog'],
            ['label' => 'Система', 'route' => 'admin.system.info', 'active' => 'admin.system.*', 'permission' => 'system.view', 'icon' => 'server'],
        ];
        
        $icons = [
            'home' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
            'document' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
            'image' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'clipboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>',
            'shopping-cart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
            'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
            'shield' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
            'cog' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'server' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>',
            'search' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
            'bell' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>',
            'menu' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>',
            'close' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
            'chevron-down' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>',
            'chevron-left' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>',
            'chevron-right' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
            'moon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>',
            'sun' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
        ];
    @endphp

    <!-- Mobile sidebar backdrop -->
    <div 
        x-show="sidebarOpen" 
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden"
        style="display: none;"
    ></div>

    <div class="min-h-screen lg:flex">
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-72 transform bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 ease-in-out lg:static lg:min-h-screen"
            :class="{ 'lg:w-18': sidebarCollapsed && !sidebarOpen }"
        >
            <!-- Logo & Collapse toggle -->
            <div class="sidebar-header flex items-center justify-between px-4 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="sidebar-logo-text overflow-hidden transition-all duration-300" x-show="!sidebarCollapsed || sidebarOpen">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">VertexCMS</p>
                    <p class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">{{ config_value('site.name', config('app.name')) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Theme toggle -->
                    <button 
                        @click="document.documentElement.setAttribute('data-theme', document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', document.documentElement.getAttribute('data-theme'))"
                        class="p-2 rounded-md text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors"
                        title="Переключить тему"
                    >
                        <svg x-show="document.documentElement.getAttribute('data-theme') !== 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="document.documentElement.getAttribute('data-theme') === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </button>
                    <!-- Collapse toggle (desktop only) -->
                    <button 
                        @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
                        class="hidden lg:flex p-2 rounded-md text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors"
                        title="Свернуть меню"
                    >
                        <svg x-show="!sidebarCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <button 
                        @click="sidebarOpen = false" 
                        class="lg:hidden p-2 rounded-md text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
                    >
                        {!! $icons['close'] !!}
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                @foreach ($navigation as $item)
                    @continue($item['permission'] && ! $user?->hasPermission($item['permission']))
                    
                    @if(isset($item['children']) && count($item['children']) > 0)
                        {{-- Menu item with dropdown --}}
                        <div x-data="{ expanded: false }">
                            <button
                                @click="expanded = !expanded"
                                class="sidebar-nav-item group flex items-center justify-between gap-3 w-full rounded-lg px-3 py-2.5 text-sm font-medium transition-all {{ request()->routeIs($item['active']) ? 'bg-slate-900 dark:bg-slate-700 text-white shadow-md' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white' }}"
                                :title="sidebarCollapsed && !sidebarOpen ? '{{ $item['label'] }}' : ''"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="sidebar-nav-icon {{ request()->routeIs($item['active']) ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}">
                                        {!! $icons[$item['icon']] !!}
                                    </span>
                                    <span class="sidebar-link-text whitespace-nowrap" x-show="!sidebarCollapsed || sidebarOpen">{{ $item['label'] }}</span>
                                </div>
                                <svg x-show="!sidebarCollapsed || sidebarOpen" class="w-4 h-4 transition-transform" :class="{'rotate-180': expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="expanded && (!sidebarCollapsed || sidebarOpen)" 
                                 x-collapse
                                 class="ml-4 mt-1 space-y-1 pl-3 border-l-2 border-slate-200 dark:border-slate-700">
                                @foreach($item['children'] as $child)
                                    @continue($child['permission'] && ! $user?->hasPermission($child['permission']))
                                    <a
                                        href="{{ route($child['route']) }}"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all {{ request()->routeIs($child['route']) ? 'bg-slate-900 dark:bg-slate-700 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white' }}"
                                    >
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- Regular menu item --}}
                        <a
                            href="{{ route($item['route']) }}"
                            class="sidebar-nav-item group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all {{ request()->routeIs($item['active']) ? 'bg-slate-900 dark:bg-slate-700 text-white shadow-md' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white' }}"
                            :title="sidebarCollapsed && !sidebarOpen ? '{{ $item['label'] }}' : ''"
                        >
                            <span class="sidebar-nav-icon {{ request()->routeIs($item['active']) ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}">
                                {!! $icons[$item['icon']] !!}
                            </span>
                            <span class="sidebar-link-text whitespace-nowrap" x-show="!sidebarCollapsed || sidebarOpen">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- User info at bottom -->
            <div class="sidebar-user-info border-t border-slate-100 dark:border-slate-700 p-4" x-show="!sidebarCollapsed || sidebarOpen">
                <div class="flex items-center gap-3 rounded-lg bg-slate-50 dark:bg-slate-700 p-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold text-sm">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $user->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email ?? '' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top header -->
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur-md">
                <div class="flex items-center justify-between gap-4 px-6 py-3">
                    <!-- Left: Menu button & Page title -->
                    <div class="flex items-center gap-4">
                        <button 
                            @click="sidebarOpen = true" 
                            class="lg:hidden p-2 rounded-md text-slate-500 hover:bg-slate-100"
                        >
                            {!! $icons['menu'] !!}
                        </button>
                        
                        <!-- Breadcrumbs -->
                        <nav class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                            @hasSection('breadcrumbs')
                                <span class="text-slate-300">/</span>
                                @yield('breadcrumbs')
                            @endif
                        </nav>
                    </div>

                    <!-- Right: Search, Notifications, User menu -->
                    <div class="flex items-center gap-3">
                        <!-- Search -->
                        <div class="relative hidden md:block">
                            <input
                                type="text"
                                placeholder="Поиск... (Ctrl+K)"
                                class="w-64 rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            >
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                {!! $icons['search'] !!}
                            </span>
                            <kbd class="absolute right-2 top-1/2 -translate-y-1/2 hidden lg:inline-flex h-5 items-center gap-1 rounded border border-slate-300 bg-white px-1.5 text-[10px] font-medium text-slate-500">
                                <span class="text-xs">⌃</span>K
                            </kbd>
                        </div>

                        <!-- Language switcher -->
                        <div class="flex items-center gap-1 border-r border-slate-200 pr-3">
                            <a href="{{ route('admin.locale.change', 'ru') }}" class="px-2 py-1 text-xs font-medium rounded {{ app()->getLocale() === 'ru' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">RU</a>
                            <a href="{{ route('admin.locale.change', 'en') }}" class="px-2 py-1 text-xs font-medium rounded {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">EN</a>
                        </div>

                        <!-- Notifications -->
                        <button class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                            {!! $icons['bell'] !!}
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                        </button>

                        <!-- User dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                @click.away="open = false"
                                class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-semibold text-xs">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="hidden md:inline">{{ $user->name ?? 'User' }}</span>
                                {!! $icons['chevron-down'] !!}
                            </button>
                            
                            <div 
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute right-0 mt-2 w-56 rounded-xl bg-white py-2 shadow-lg border border-slate-200 z-50"
                                style="display: none;"
                            >
                                <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Профиль
                                </a>
                                <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Настройки
                                </a>
                                <hr class="my-2 border-slate-100">
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        {{ __('admin.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 px-6 py-6 overflow-y-auto">
                <!-- Page header with title and actions -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">@yield('page_title', __('admin.dashboard'))</h1>
                            @hasSection('page_subtitle')
                                <p class="mt-1 text-sm text-slate-500">@yield('page_subtitle')</p>
                            @endif
                        </div>
                        @hasSection('page_actions')
                            <div class="flex items-center gap-3">
                                @yield('page_actions')
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Flash messages -->
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('status') }}
                        <button @click="$el.parentElement.remove()" class="ml-auto text-emerald-600 hover:text-emerald-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('error') }}
                        <button @click="$el.parentElement.remove()" class="ml-auto text-red-600 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        {{ session('warning') }}
                        <button @click="$el.parentElement.remove()" class="ml-auto text-amber-600 hover:text-amber-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Keyboard shortcuts modal (hidden by default) -->
    <div 
        x-show="searchOpen"
        @keydown.escape.window="searchOpen = false"
        class="fixed inset-0 z-50 flex items-start justify-center pt-24 bg-slate-900/50 backdrop-blur-sm"
        style="display: none;"
    >
        <div 
            class="w-full max-w-2xl rounded-xl bg-white shadow-2xl border border-slate-200"
            @click.away="searchOpen = false"
        >
            <div class="flex items-center border-b border-slate-200 px-4 py-3">
                {!! $icons['search'] !!}
                <input
                    type="text"
                    placeholder="Поиск по админ-панели..."
                    class="flex-1 ml-3 text-base focus:outline-none"
                    autofocus
                >
                <kbd class="rounded border border-slate-300 bg-slate-50 px-2 py-1 text-xs text-slate-500">ESC</kbd>
            </div>
            <div class="p-2">
                <div class="px-3 py-2 text-xs font-medium text-slate-500 uppercase">Быстрые действия</div>
                <a href="{{ route('admin.pages.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Создать страницу
                </a>
                <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Добавить пользователя
                </a>
            </div>
        </div>
    </div>

    <script>
        // Initialize theme from localStorage
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
        
        // Initialize sidebar collapsed state from localStorage
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebarState', () => ({
                sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
                init() {
                    this.$watch('sidebarCollapsed', value => {
                        localStorage.setItem('sidebarCollapsed', value);
                    });
                }
            }));
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+K or Cmd+K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.dispatchEvent(new CustomEvent('toggle-search'));
            }
        });
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalSearch', () => ({
                searchOpen: false,
                init() {
                    window.addEventListener('toggle-search', () => {
                        this.searchOpen = !this.searchOpen;
                    });
                }
            }));
        });
    </script>
</body>
</html>
