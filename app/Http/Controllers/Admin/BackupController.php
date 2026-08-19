<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\System\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        return view('admin.system.backups');
    }

    /**
     * Get list of backups via API
     */
    public function apiList()
    {
        try {
            $backups = $this->backupService->listBackups();
            
            return response()->json([
                'success' => true,
                'backups' => $backups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new backup via API
     */
    public function apiCreate(Request $request)
    {
        $type = $request->input('type', 'database');
        
        try {
            if ($type === 'database') {
                $result = $this->backupService->createDatabaseBackup();
            } else {
                $result = $this->backupService->createFilesBackup();
            }

            return response()->json([
                'success' => true,
                'message' => 'Бэкап успешно создан',
                'backup' => $result
            ]);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function apiDownload($filename)
    {
        try {
            $path = 'backups/' . $filename;
            
            if (!Storage::disk('local')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Файл не найден'
                ], 404);
            }

            return Storage::disk('local')->download($path);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore from backup via API
     */
    public function apiRestore(Request $request)
    {
        $file = $request->input('file');
        
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан файл для восстановления'
            ], 400);
        }

        try {
            $backupType = str_starts_with($file, 'db_') ? 'database' : 'files';
            
            if ($backupType === 'database') {
                $this->backupService->restoreDatabase($file);
            } else {
                $this->backupService->restoreFiles($file);
            }

            return response()->json([
                'success' => true,
                'message' => 'Восстановление успешно завершено'
            ]);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete backup via API
     */
    public function apiDelete($filename)
    {
        try {
            $path = 'backups/' . $filename;
            
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            return response()->json([
                'success' => true,
                'message' => 'Бэкап удален'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup schedule settings
     */
    public function getSchedule()
    {
        $schedule = config('backup.schedule', [
            'database' => 'daily',
            'files' => 'weekly',
            'retention' => 30,
            'storage' => 'local'
        ]);

        return response()->json([
            'success' => true,
            'schedule' => $schedule
        ]);
    }

    /**
     * Save backup schedule settings
     * FIX C04: Implement actual schedule persistence to database
     */
    public function saveSchedule(Request $request)
    {
        try {
            $validated = $request->validate([
                'database_frequency' => 'nullable|string|in:daily,weekly,monthly',
                'files_frequency' => 'nullable|string|in:daily,weekly,monthly',
                'retention_days' => 'nullable|integer|min:1|max:365',
                'storage_disk' => 'nullable|string|in:local,s3,minio',
                'enabled' => 'nullable|boolean',
            ]);

            // Save to settings table using Laravel's config repository
            $settings = [
                'backup.schedule.database' => $validated['database_frequency'] ?? 'daily',
                'backup.schedule.files' => $validated['files_frequency'] ?? 'weekly',
                'backup.schedule.retention' => $validated['retention_days'] ?? 30,
                'backup.schedule.storage' => $validated['storage_disk'] ?? config('filesystems.default', 'local'),
                'backup.schedule.enabled' => $validated['enabled'] ?? true,
            ];

            // Persist each setting to the database
            foreach ($settings as $key => $value) {
                \App\Models\Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => is_bool($value) ? ($value ? 'true' : 'false') : $value]
                );
            }

            // Clear cached settings
            Cache::forget('settings.backup');

            // Log the change
            Log::info('Backup schedule updated', [
                'settings' => $settings,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Настройки расписания бэкапов сохранены',
                'settings' => $settings,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации данных',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении настроек: ' . $e->getMessage(),
            ], 500);
        }
    }
}
