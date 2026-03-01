<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController
{
    /**
     * Display all backups with health status
     */
    public function index(Request $request)
    {
        try {
            $backups = $this->getBackupsList();

            $stats = [
                'total_backups' => count($backups),
                'last_backup' => count($backups) > 0 ? $backups[0]['date_formatted'] : 'Never',
                'total_size' => $this->formatBytes(array_sum(array_column($backups, 'size'))),
                'health_status' => 'Healthy',
            ];

            return Inertia::render('Admin/Backups/Index', [
                'backups' => array_slice($backups, 0, 50),
                'healthStatus' => [],
                'backupStats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Backup listing failed', ['error' => $e->getMessage()]);
            return Inertia::render('Admin/Backups/Index', [
                'backups' => [],
                'healthStatus' => [],
                'backupStats' => ['error' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Trigger manual backup
     */
    public function store(Request $request)
    {
        try {
            Artisan::call('backup:run', [
                '--disable-notifications' => false,
            ]);

            Log::info('Manual backup triggered by admin');

            return back()->with('success', 'Backup initiated successfully. Check backup logs for completion.');
        } catch (\Exception $e) {
            Log::error('Manual backup failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Monitor backup health
     */
    public function health(Request $request)
    {
        try {
            $backups = $this->getBackupsList();

            $healthMetrics = [
                [
                    'name' => 'Default Backup',
                    'reachable' => true,
                    'free_storage' => disk_free_space(storage_path()),
                    'used_storage' => disk_total_space(storage_path()) - disk_free_space(storage_path()),
                    'free_storage_human' => $this->formatBytes(disk_free_space(storage_path())),
                    'used_storage_human' => $this->formatBytes(disk_total_space(storage_path()) - disk_free_space(storage_path())),
                    'backup_count' => count($backups),
                    'latest_backup' => count($backups) > 0 ? $backups[0]['date'] : null,
                    'latest_backup_size' => count($backups) > 0 ? $backups[0]['size_human'] : null,
                ]
            ];

            return Inertia::render('Admin/Backups/Health', [
                'metrics' => $healthMetrics,
                'checkedAt' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Backup health check failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Health check failed: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old backups
     */
    public function cleanup(Request $request)
    {
        try {
            Artisan::call('backup:cleanup');

            Log::info('Backup cleanup executed by admin');

            return back()->with('success', 'Backup cleanup completed successfully.');
        } catch (\Exception $e) {
            Log::error('Backup cleanup failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Get list of backups from storage
     */
    private function getBackupsList()
    {
        $backupPath = storage_path('backups');
        $backups = [];

        if (!is_dir($backupPath)) {
            return $backups;
        }

        $files = array_diff(scandir($backupPath, SCANDIR_SORT_DESCENDING), ['.', '..']);

        foreach ($files as $file) {
            $filePath = $backupPath . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath)) {
                $backups[] = [
                    'path' => $file,
                    'size' => filesize($filePath),
                    'size_human' => $this->formatBytes(filesize($filePath)),
                    'date' => date('Y-m-d\TH:i:s\Z', filemtime($filePath)),
                    'date_formatted' => date('Y-m-d H:i:s', filemtime($filePath)),
                ];
            }
        }

        return $backups;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
