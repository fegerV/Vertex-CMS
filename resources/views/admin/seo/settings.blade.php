@extends('admin.layouts.app')

@section('title', 'Настройки SEO')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Настройки SEO</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-1">Глобальные настройки SEO для всего сайта</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.seo.settings.update') }}" class="max-w-4xl">
        @csrf
        
        <!-- Общие настройки -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Общие настройки</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Разделитель заголовка
                    </label>
                    <select name="title_separator" 
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="|">| (вертикальная черта)</option>
                        <option value="-">- (дефис)</option>
                        <option value="•">• (точка)</option>
                        <option value=":">: (двоеточие)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Шаблон Title по умолчанию
                    </label>
                    <input type="text" name="default_title_template" 
                           placeholder="%title% | %sitename%"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Доступные переменные: %title%, %sitename%, %tagline%</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Шаблон Description по умолчанию
                    </label>
                    <textarea name="default_description_template" rows="2"
                              placeholder="Описание сайта..."
                              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Социальные сети -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Социальные сети (Open Graph)</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Изображение по умолчанию для соцсетей
                    </label>
                    <input type="text" name="og_default_image" 
                           placeholder="https://example.com/image.jpg"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Тип сайта по умолчанию
                    </label>
                    <select name="og_default_type" 
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="website">Website</option>
                        <option value="article">Article</option>
                        <option value="blog">Blog</option>
                        <option value="product">Product</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Вебмастера -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Коды подтверждения вебмастеров</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Google Search Console
                    </label>
                    <input type="text" name="google_verification" 
                           placeholder="googlexxxxxxxxxxxx.html"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Яндекс.Вебмастер
                    </label>
                    <input type="text" name="yandex_verification" 
                           placeholder="yandex_xxxxxxxxxxxx"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Bing Webmaster
                    </label>
                    <input type="text" name="bing_verification" 
                           placeholder="BingSiteAuth.xml"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Дополнительные настройки -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Дополнительно</h2>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="enable_sitemap" value="1" 
                           id="enable_sitemap"
                           class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="enable_sitemap" class="ml-2 block text-sm text-slate-700 dark:text-slate-300">
                        Включить карту сайта (sitemap.xml)
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="enable_robots" value="1" 
                           id="enable_robots"
                           class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="enable_robots" class="ml-2 block text-sm text-slate-700 dark:text-slate-300">
                        Использовать robots.txt
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="noindex_empty_archives" value="1" 
                           id="noindex_empty_archives"
                           class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="noindex_empty_archives" class="ml-2 block text-sm text-slate-700 dark:text-slate-300">
                        Noindex для пустых архивов
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Сохранить настройки
            </button>
        </div>
    </form>
</div>
@endsection
