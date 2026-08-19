@extends('admin.layouts.app')

@section('title', 'Роботы и Файлы - SEO')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Роботы и Файлы</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-1">Управление robots.txt, .htaccess и sitemap.xml</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Robots.txt Editor -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">robots.txt</h2>
            <form method="POST" action="{{ route('admin.seo.robots.update') }}">
                @csrf
                <textarea name="robots_txt" 
                          rows="15"
                          class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">{{ old('robots_txt', $robotsContent ?? '') }}</textarea>
                <button type="submit" 
                        class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Сохранить robots.txt
                </button>
            </form>
        </div>

        <!-- .htaccess Editor -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">.htaccess</h2>
            <form method="POST" action="{{ route('admin.seo.robots.update') }}">
                @csrf
                <textarea name="htaccess" 
                          rows="15"
                          class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">{{ old('htaccess', $htaccessContent ?? '') }}</textarea>
                <button type="submit" 
                        class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Сохранить .htaccess
                </button>
            </form>
        </div>
    </div>

    <!-- Sitemap Section -->
    <div class="mt-6 bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Карта сайта (sitemap.xml)</h2>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 dark:text-slate-400">
                    Карта сайта автоматически генерируется на основе опубликованных страниц.
                </p>
                <a href="{{ url('/sitemap.xml') }}" target="_blank" 
                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mt-2 inline-block">
                    Просмотреть sitemap.xml →
                </a>
            </div>
            <form method="GET" action="{{ route('admin.seo.sitemap.generate') }}">
                @csrf
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Обновить карту сайта
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
