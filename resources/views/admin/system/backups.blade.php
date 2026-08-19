@extends('admin.layouts.app')

@section('title', 'Резервное копирование')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Резервное копирование</h1>
        <button @click="createBackup()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Создать бэкап
        </button>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Всего бэкапов</p>
                    <p class="text-2xl font-bold text-gray-800" x-text="backups.length"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Последний бэкап</p>
                    <p class="text-lg font-semibold text-gray-800" x-text="lastBackup"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 0c0 2.21-3.582 4-8 4S8 9.79 8 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Общий размер</p>
                    <p class="text-2xl font-bold text-gray-800" x-text="totalSize"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица бэкапов -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Список бэкапов</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя файла</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Размер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата создания</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="backup in backups" :key="backup.name">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="backup.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 text-xs rounded-full" 
                                      :class="backup.type === 'database' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                      x-text="backup.type === 'database' ? 'База данных' : 'Файлы'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatSize(backup.size)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(backup.created_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="downloadBackup(backup.name)" class="text-blue-600 hover:text-blue-900 mr-3">
                                    Скачать
                                </button>
                                <button @click="restoreBackup(backup.name)" class="text-green-600 hover:text-green-900 mr-3">
                                    Восстановить
                                </button>
                                <button @click="deleteBackup(backup.name)" class="text-red-600 hover:text-red-900">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="backups.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Бэкапы не найдены. Создайте первый бэкап.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Настройки расписания -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Настройки автоматического бэкапа</h2>
        <form @submit.prevent="saveSchedule">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Частота бэкапа БД</label>
                    <select x-model="schedule.database" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="disabled">Отключено</option>
                        <option value="hourly">Ежечасно</option>
                        <option value="daily">Ежедневно</option>
                        <option value="weekly">Еженедельно</option>
                        <option value="monthly">Ежемесячно</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Частота бэкапа файлов</label>
                    <select x-model="schedule.files" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="disabled">Отключено</option>
                        <option value="daily">Ежедневно</option>
                        <option value="weekly">Еженедельно</option>
                        <option value="monthly">Ежемесячно</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Хранить (дней)</label>
                    <input type="number" x-model="schedule.retention" min="1" max="365" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Хранилище</label>
                    <select x-model="schedule.storage" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="local">Локальное</option>
                        <option value="s3">Amazon S3</option>
                        <option value="gcs">Google Cloud Storage</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                    Сохранить настройки
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('backupManager', () => ({
        backups: [],
        schedule: {
            database: 'daily',
            files: 'weekly',
            retention: 30,
            storage: 'local'
        },
        lastBackup: 'Никогда',
        totalSize: '0 MB',

        init() {
            this.loadBackups();
            this.loadSchedule();
        },

        async loadBackups() {
            try {
                const response = await fetch('/admin/api/backups');
                const data = await response.json();
                this.backups = data.backups || [];
                
                if (this.backups.length > 0) {
                    const latest = this.backups[0];
                    this.lastBackup = new Date(latest.created_at * 1000).toLocaleDateString('ru-RU');
                    
                    const totalBytes = this.backups.reduce((sum, b) => sum + b.size, 0);
                    this.totalSize = this.formatSize(totalBytes);
                }
            } catch (error) {
                console.error('Error loading backups:', error);
            }
        },

        async loadSchedule() {
            try {
                const response = await fetch('/admin/api/backup-schedule');
                const data = await response.json();
                if (data.schedule) {
                    this.schedule = { ...this.schedule, ...data.schedule };
                }
            } catch (error) {
                console.error('Error loading schedule:', error);
            }
        },

        async createBackup() {
            const type = confirm('Создать бэкап базы данных? Отмена для бэкапа файлов.') 
                ? 'database' 
                : 'files';
            
            try {
                const response = await fetch('/admin/api/backups/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ type })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Бэкап успешно создан!');
                    this.loadBackups();
                } else {
                    alert('Ошибка создания бэкапа: ' + result.message);
                }
            } catch (error) {
                console.error('Error creating backup:', error);
                alert('Ошибка создания бэкапа');
            }
        },

        async downloadBackup(filename) {
            window.location.href = `/admin/api/backups/download/${encodeURIComponent(filename)}`;
        },

        async restoreBackup(filename) {
            if (!confirm(`Вы уверены, что хотите восстановить из бэкапа "${filename}"? Это может перезаписать текущие данные.`)) {
                return;
            }

            try {
                const response = await fetch('/admin/api/backups/restore', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ file: filename })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Восстановление успешно завершено!');
                } else {
                    alert('Ошибка восстановления: ' + result.message);
                }
            } catch (error) {
                console.error('Error restoring backup:', error);
                alert('Ошибка восстановления');
            }
        },

        async deleteBackup(filename) {
            if (!confirm(`Вы уверены, что хотите удалить бэкап "${filename}"?`)) {
                return;
            }

            try {
                const response = await fetch(`/admin/api/backups/${encodeURIComponent(filename)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.loadBackups();
                } else {
                    alert('Ошибка удаления: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting backup:', error);
                alert('Ошибка удаления');
            }
        },

        async saveSchedule() {
            try {
                const response = await fetch('/admin/api/backup-schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.schedule)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Настройки сохранены!');
                } else {
                    alert('Ошибка сохранения: ' + result.message);
                }
            } catch (error) {
                console.error('Error saving schedule:', error);
                alert('Ошибка сохранения настроек');
            }
        },

        formatSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            while (bytes >= 1024 && i < units.length - 1) {
                bytes /= 1024;
                i++;
            }
            return bytes.toFixed(2) + ' ' + units[i];
        },

        formatDate(timestamp) {
            return new Date(timestamp * 1000).toLocaleString('ru-RU');
        }
    }));
});
</script>
@endpush

@endsection
