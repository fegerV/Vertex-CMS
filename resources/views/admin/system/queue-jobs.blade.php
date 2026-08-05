@extends('admin.layouts.app')

@section('title', 'Задачи очереди: ' . $queue)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Очередь: {{ $queue }}</h1>
        <a href="{{ route('admin.system.queues') }}" class="text-indigo-600 hover:text-indigo-900">
            ← Вернуться к очередям
        </a>
    </div>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Задачи в очереди ({{ count($jobs) }})</h2>
        </div>

        @if(count($jobs) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Задача</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Данные</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($jobs as $job)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $job['id'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $job['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <pre class="text-xs bg-gray-100 p-2 rounded overflow-auto max-w-md">{{ json_encode($job['payload'], JSON_PRETTY_PRINT) }}</pre>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                Очередь пуста
            </div>
        @endif
    </div>
</div>
@endsection
