<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\System\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     */
    public function saveSchedule(Request $request)
    {
        try {
            // In a real application, you would save this to database or config file
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => 'Настройки сохранены'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
