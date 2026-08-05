@extends('admin.layouts.app')

@section('title', 'Мониторинг очередей')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Мониторинг очередей задач</h1>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach($queues as $name => $count)
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">{{ $name }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $count }}</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.system.queues.show', $name) }}" 
                   class="mt-4 block text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                    Просмотреть задачи →
                </a>
            </div>
        @endforeach
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Неудачные задачи ({{ count($failedJobs) }})</h2>
            @if(count($failedJobs) > 0)
                <form action="{{ route('admin.system.queues.flush-failed') }}" method="POST" 
                      onsubmit="return confirm('Удалить все неудачные задачи?')">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                        Очистить все
                    </button>
                </form>
            @endif
        </div>

        @if(count($failedJobs) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Очередь</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Задача</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ошибка</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата сбоя</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(array_slice($failedJobs, 0, 10) as $job)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $job->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $job->connection }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $job->payload['displayName'] ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate">
                                    {{ Str::limit($job->exception, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $job->failed_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('admin.system.queues.retry-failed', $job->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Повторить</button>
                                    </form>
                                    <form action="{{ route('admin.system.queues.delete-failed', $job->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900">Удалить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                Нет неудачных задач
            </div>
        @endif
    </div>

    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-blue-800 font-semibold mb-2">Информация</h3>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• Для работы очередей необходимо настроить Redis в config/database.php</li>
            <li>• Запустите воркер командой: <code class="bg-blue-100 px-2 py-1 rounded">php artisan queue:work</code></li>
            <li>• Для обработки конкретной очереди: <code class="bg-blue-100 px-2 py-1 rounded">php artisan queue:work --queue=emails</code></li>
        </ul>
    </div>
</div>
@endsection
