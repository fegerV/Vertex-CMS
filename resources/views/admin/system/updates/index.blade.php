@extends('admin.layout')

@section('title', 'Обновления системы')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Обновления системы</h1>
            <p class="text-gray-600 dark:text-gray-400">Управление версиями и автоматическое обновление CMS</p>
        </div>

        <!-- Текущая версия и статус -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Текущая версия</h2>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $updateInfo['current_version'] }}</p>
                </div>
                <div class="text-right">
                    @if($updateInfo['available'])
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012-2h1a1 1 0 100-2h-1a4 4 0 100 8h1a1 1 0 100-2h-1a2 2 0 01-2-2z" clip-rule="evenodd"/>
                            </svg>
                            Доступно обновление
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Актуальная версия
                        </span>
                    @endif
                </div>
            </div>

            @if($updateInfo['available'])
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Доступна версия {{ $updateInfo['latest_version'] }}
                            </h3>
                            @if(!empty($updateInfo['changelog']))
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                    <p class="font-medium mb-1">Что нового:</p>
                                    <div class="prose prose-sm max-w-none">{{ $updateInfo['changelog'] }}</div>
                                </div>
                            @endif
                            @if($updateInfo['critical'] ?? false)
                                <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">
                                    ⚠️ Критическое обновление безопасности
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Действия -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Действия</h3>
            
            <div class="space-y-4">
                @if($updateInfo['available'] && !empty($updateInfo['download_url']))
                    <button id="updateBtn" onclick="startUpdate()" 
                            class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="updateSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Обновить до версии {{ $updateInfo['latest_version'] }}
                    </button>
                    
                    <div id="updateProgress" class="hidden">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                            <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-sm text-gray-600 dark:text-gray-400 text-center">Загрузка обновления...</p>
                    </div>
                @endif

                <button onclick="checkForUpdates()" 
                        class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Проверить обновления
                </button>

                <form action="{{ route('admin.system.optimize') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Оптимизировать систему (очистка кэша)
                    </button>
                </form>
            </div>
        </div>

        <!-- История обновлений -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">История обновлений</h3>
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                История обновлений будет доступна после первого обновления системы
            </div>
        </div>
    </div>
</div>

<script>
function checkForUpdates() {
    fetch('{{ route("admin.system.check") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        alert('Ошибка проверки обновлений: ' + error);
    });
}

function startUpdate() {
    if (!confirm('Вы уверены, что хотите обновить систему? Рекомендуется создать резервную копию перед обновлением.')) {
        return;
    }

    const btn = document.getElementById('updateBtn');
    const spinner = document.getElementById('updateSpinner');
    const progress = document.getElementById('updateProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    progress.classList.remove('hidden');

    let progressValue = 0;
    const interval = setInterval(() => {
        if (progressValue < 90) {
            progressValue += 10;
            progressBar.style.width = progressValue + '%';
        }
    }, 500);

    fetch('{{ route("admin.system.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            download_url: '{{ $updateInfo["download_url"] }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(interval);
        progressBar.style.width = '100%';
        
        if (data.success) {
            progressText.textContent = 'Обновление успешно завершено! Перезагрузка...';
            progressText.classList.add('text-green-600');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            progressText.textContent = 'Ошибка: ' + data.message;
            progressText.classList.add('text-red-600');
            btn.disabled = false;
            spinner.classList.add('hidden');
            
            if (data.rolled_back) {
                progressText.textContent += ' Изменения откатаны.';
            }
        }
    })
    .catch(error => {
        clearInterval(interval);
        progressText.textContent = 'Критическая ошибка: ' + error;
        progressText.classList.add('text-red-600');
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}
</script>
@endsection
