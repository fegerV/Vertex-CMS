@extends('admin.layouts.app')

@section('title', 'IP Фильтры')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Управление IP-фильтрами</h1>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Добавить IP-фильтр</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.security.ip-filters.store') }}" method="POST" class="grid grid-cols-5 gap-4">
                @csrf
                <input type="hidden" name="active" value="1">
                
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP адрес</label>
                    <input type="text" name="ip_address" id="ip_address" placeholder="192.168.1.1 или 192.168.*.*"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                    <select name="type" id="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="blacklist">Чёрный список</option>
                        <option value="whitelist">Белый список</option>
                    </select>
                </div>

                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Причина</label>
                    <input type="text" name="reason" id="reason" placeholder="Необязательно"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Истекает</label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Список фильтров</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.security.ip-filters', ['type' => 'blacklist']) }}" 
                   class="px-3 py-1 text-sm rounded {{ request('type') === 'blacklist' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                    Чёрный список
                </a>
                <a href="{{ route('admin.security.ip-filters', ['type' => 'whitelist']) }}" 
                   class="px-3 py-1 text-sm rounded {{ request('type') === 'whitelist' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    Белый список
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP адрес</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Причина</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Истекает</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата добавления</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($filters as $filter)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $filter->ip_address }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $filter->type === 'blacklist' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $filter->type === 'blacklist' ? 'Чёрный' : 'Белый' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $filter->reason ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $filter->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $filter->isActive() ? 'Активен' : 'Неактивен' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $filter->expires_at ? $filter->expires_at->format('d.m.Y H:i') : 'Бессрочно' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $filter->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editFilter({{ $filter->id }}, '{{ $filter->ip_address }}', '{{ $filter->type }}', '{{ $filter->reason }}', '{{ $filter->expires_at?->format('Y-m-d\TH:i') }}')"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">Изменить</button>
                                <form action="{{ route('admin.security.ip-filters.destroy', $filter) }}" method="POST" class="inline" onsubmit="return confirm('Удалить этот фильтр?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Фильтры не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($filters->hasPages())
            <div class="p-6 border-t">
                {{ $filters->links() }}
            </div>
        @endif
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-semibold mb-4">Редактировать фильтр</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">IP адрес</label>
                <input type="text" name="ip_address" id="edit_ip_address" class="w-full rounded-md border-gray-300">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                <select name="type" id="edit_type" class="w-full rounded-md border-gray-300">
                    <option value="blacklist">Чёрный список</option>
                    <option value="whitelist">Белый список</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Причина</label>
                <input type="text" name="reason" id="edit_reason" class="w-full rounded-md border-gray-300">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Истекает</label>
                <input type="datetime-local" name="expires_at" id="edit_expires_at" class="w-full rounded-md border-gray-300">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" 
                        class="px-4 py-2 bg-gray-200 rounded-md">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
function editFilter(id, ip, type, reason, expiresAt) {
    document.getElementById('editForm').action = '/admin/security/ip-filters/' + id;
    document.getElementById('edit_ip_address').value = ip;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_reason').value = reason;
    document.getElementById('edit_expires_at').value = expiresAt || '';
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection
