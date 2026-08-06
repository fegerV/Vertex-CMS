<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ошибка')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 flex items-center justify-center min-h-screen">
    <div class="text-center px-4">
        <h1 class="text-9xl font-bold text-indigo-600 dark:text-indigo-400">@yield('code', '500')</h1>
        <h2 class="text-3xl font-semibold mt-4 mb-2">@yield('message', 'Что-то пошло не так')</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">@yield('description', 'Наши специалисты уже работают над решением проблемы. Попробуйте обновить страницу позже.')</p>
        
        <div class="flex justify-center gap-4">
            <a href="/" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition shadow-lg">
                На главную
            </a>
            <button onclick="window.location.reload()" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition">
                Обновить страницу
            </button>
        </div>
        
        @if(config('app.debug'))
            <div class="mt-12 text-left bg-white dark:bg-gray-800 p-6 rounded-lg shadow-inner max-w-2xl mx-auto overflow-auto">
                <h3 class="text-lg font-bold mb-2">Debug Info:</h3>
                <pre class="text-xs text-red-500">@yield('trace', '')</pre>
            </div>
        @endif
    </div>
</body>
</html>
