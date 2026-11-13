<?php

namespace App\Controllers;

use App\Services\DatabaseBackupService;

class BackupController extends BaseController
{
    protected DatabaseBackupService $backupService;

    public function __construct()
    {
        $this->backupService = new DatabaseBackupService();
    }

    /**
     * Backup Dashboard & Management Index
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // 1. Fetch DB Backups history records
        $backups = $db->table('db_backups')
            ->select('db_backups.*, users.name as creator_name')
            ->join('users', 'users.id = db_backups.created_by', 'left')
            ->orderBy('db_backups.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Check file existence on disk for each record
        $backupDir = $this->backupService->getBackupDir();
        foreach ($backups as &$b) {
            $fullPath = $backupDir . $b['filename'];
            $b['exists_on_disk'] = file_exists($fullPath);
            if ($b['exists_on_disk'] && empty($b['file_size'])) {
                $b['file_size'] = filesize($fullPath);
            }
        }

        // 2. Fetch all physical .sql and .zip files in backup/ folder
        $scannedFiles = [];
        if (is_dir($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $f) {
                $lower = strtolower($f);
                if ($f !== '.' && $f !== '..' && (str_ends_with($lower, '.sql') || str_ends_with($lower, '.zip'))) {
                    $scannedFiles[$f] = [
                        'filename'  => $f,
                        'file_size' => filesize($backupDir . $f),
                        'modified'  => filemtime($backupDir . $f),
                    ];
                }
            }
        }

        // 3. Current database summary
        $tables = $db->listTables();
        $totalTables = count($tables);
        $totalRows = 0;
        foreach ($tables as $t) {
            $totalRows += (int) $db->table($t)->countAllResults();
        }

        // 4. Backup Configuration Settings
        $settingsQuery = $db->table('settings')->like('setting_key', 'backup_', 'after')->get()->getResultArray();
        $config = [];
        foreach ($settingsQuery as $row) {
            $config[$row['setting_key']] = $row['setting_value'];
        }

        $data = [
            'title'        => 'Database Backup & Restore',
            'backups'      => $backups,
            'scannedFiles' => $scannedFiles,
            'totalTables'  => $totalTables,
            'tablesCount'  => $totalTables,
            'totalRows'    => $totalRows,
            'dbName'       => $db->getDatabase(),
            'config'       => $config,
            'settings'     => $config,
        ];

        return view('settings/backup/index', $data);
    }

    /**
     * Trigger New Backup Manually
     */
    public function create()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        try {
            $userId = session()->get('user_id') ? (int) session()->get('user_id') : null;
            $res = $this->backupService->createBackup('manual', $userId);

            $msg = "Database backup `{$res['filename']}` created successfully! (" . round($res['file_size'] / (1024 * 1024), 2) . " MB)";
            if (!empty($res['email']['success'])) {
                $msg .= " &bull; Sent via email.";
            } elseif (!empty($res['email']['error'])) {
                $msg .= " (Email warning: {$res['email']['error']})";
            }
            if (!empty($res['gdrive']['success'])) {
                $msg .= " &bull; Uploaded to Google Drive.";
            } elseif (!empty($res['gdrive']['error'])) {
                $msg .= " (Drive warning: {$res['gdrive']['error']})";
            }

            return redirect()->to('settings/backup')->with('success', $msg);
        } catch (\Throwable $e) {
            log_message('error', 'BackupController::create error: ' . $e->getMessage());
            return redirect()->to('settings/backup')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Resolve backup file path and filename from ID or raw filename
     */
    protected function resolveBackupFile(string $idOrFilename): ?array
    {
        $backupDir = $this->backupService->getBackupDir();
        $filename = null;
        $backupRow = null;

        // If numeric ID, query the db_backups table
        if (ctype_digit($idOrFilename)) {
            $db = \Config\Database::connect();
            $backupRow = $db->table('db_backups')->where('id', (int) $idOrFilename)->get()->getRowArray();
            if ($backupRow && !empty($backupRow['filename'])) {
                $filename = basename($backupRow['filename']);
            }
        }

        if (!$filename) {
            $filename = basename($idOrFilename);
        }

        $filePath = $backupDir . $filename;
        if (!file_exists($filePath)) {
            return null;
        }

        return [
            'id'       => $backupRow['id'] ?? null,
            'filename' => $filename,
            'filepath' => $filePath,
            'row'      => $backupRow,
        ];
    }

    /**
     * Download SQL Backup File
     */
    public function download(string $idOrFilename)
    {
        ini_set('memory_limit', '1024M');

        $resolved = $this->resolveBackupFile($idOrFilename);
        if (!$resolved) {
            return redirect()->to('settings/backup')->with('error', 'File not found on disk.');
        }

        return $this->response->download($resolved['filepath'], null)->setFileName($resolved['filename']);
    }

    /**
     * Dedicated Database Restore Page with Progress & Restored History
     */
    public function restorePage()
    {
        $db = \Config\Database::connect();

        // 1. Fetch restore history records from db_restores table
        $dbRecords = [];
        $recordedLogFiles = [];
        try {
            $dbRecords = $db->table('db_restores')
                ->select('db_restores.*, users.name as user_name')
                ->join('users', 'users.id = db_restores.restored_by', 'left')
                ->orderBy('db_restores.created_at', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($dbRecords as $r) {
                if (!empty($r['log_file'])) {
                    $recordedLogFiles[] = basename($r['log_file']);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error reading db_restores table: ' . $e->getMessage());
        }

        // 2. Scan physical log files in writable/logs/restore_*.log to recover history if database was overwritten
        $scannedLogEntries = [];
        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        if (is_dir($logDir)) {
            $files = glob($logDir . 'restore_*.log');
            if ($files) {
                // Sort newest first
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });

                foreach ($files as $filePath) {
                    $base = basename($filePath);
                    if (in_array($base, $recordedLogFiles, true)) {
                        continue; // Already present in database records
                    }

                    // Parse header and stats from log file
                    $content = @file_get_contents($filePath);
                    $originalFile = $base;
                    $restoredAt = date('Y-m-d H:i:s', filemtime($filePath));
                    $status = str_contains($content, 'Restore complete!') ? 'completed' : (str_contains($content, 'Fatal Error:') || str_contains($content, 'Restore Error:') ? 'failed' : 'completed');
                    $tablesCount = 0;
                    $rowsCount = 0;

                    if (preg_match('/Original File:\s*([^\r\n]+)/i', $content, $m)) {
                        $originalFile = trim($m[1]);
                    }
                    if (preg_match('/Timestamp:\s*([^\r\n]+)/i', $content, $m)) {
                        $restoredAt = trim($m[1]);
                    }
                    if (preg_match('/Total tables:\s*([0-9]+)/i', $content, $m)) {
                        $tablesCount = (int) $m[1];
                    }
                    if (preg_match('/Total rows(?: restored)?:\s*([0-9,]+)/i', $content, $m)) {
                        $rowsCount = (int) str_replace(',', '', $m[1]);
                    }

                    $scannedLogEntries[] = [
                        'id'                => 'file_' . md5($base),
                        'filename'          => $base,
                        'original_filename' => $originalFile,
                        'file_size'         => filesize($filePath),
                        'status'            => $status,
                        'tables_count'      => $tablesCount,
                        'rows_count'        => $rowsCount,
                        'duration_seconds'  => 0,
                        'error_message'     => null,
                        'log_file'          => $base,
                        'user_name'         => 'System / Admin',
                        'created_at'        => $restoredAt,
                        'is_file_only'      => true,
                    ];
                }
            }
        }

        // Merge database records and scanned physical log files
        $allRestores = array_merge($dbRecords, $scannedLogEntries);

        // Sort all by created_at DESC
        usort($allRestores, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $data = [
            'title'    => 'Restore Database from Backup',
            'dbName'   => $db->getDatabase(),
            'restores' => $allRestores,
        ];

        return view('settings/backup/restore', $data);
    }

    /**
     * View Stored Restore Log (Raw or JSON)
     */
    public function viewRestoreLog($idOrToken)
    {
        $db = \Config\Database::connect();
        $record = null;
        $logPath = null;

        // If numeric ID, query db_restores table
        if (is_numeric($idOrToken)) {
            $record = $db->table('db_restores')
                ->select('db_restores.*, users.name as user_name')
                ->join('users', 'users.id = db_restores.restored_by', 'left')
                ->where('db_restores.id', (int) $idOrToken)
                ->get()
                ->getRowArray();

            if ($record && !empty($record['log_file'])) {
                $logPath = WRITEPATH . 'logs/' . basename($record['log_file']);
            }
        } else {
            // Check if looking up by file_ md5 token or raw filename
            $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
            $files = glob($logDir . 'restore_*.log');
            if ($files) {
                foreach ($files as $f) {
                    $base = basename($f);
                    if ('file_' . md5($base) === $idOrToken || $base === $idOrToken) {
                        $logPath = $f;
                        $record = [
                            'original_filename' => $base,
                            'log_file'          => $base,
                        ];
                        break;
                    }
                }
            }
        }

        if (!$record && !$logPath) {
            return $this->response->setStatusCode(404)->setBody('Restore record not found.');
        }

        $logContent = '';
        if ($logPath && file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
        } else {
            $logContent = "Log file not found on disk.";
            if (!empty($record['error_message'])) {
                $logContent .= "\nError Message recorded: " . $record['error_message'];
            }
        }

        if ($this->request->isAJAX() || $this->request->getGet('json') === '1') {
            return $this->response->setJSON([
                'success' => true,
                'record'  => $record,
                'log'     => $logContent,
            ]);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody($logContent);
    }

    /**
     * AJAX Endpoint for Upload & Restore with Log Tracking
     */
    public function uploadAndRestoreAjax()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $startTime = microtime(true);
        $logs = [];
        $logCallback = function(string $msg, int $pct) use (&$logs) {
            $logs[] = [
                'time'    => date('H:i:s'),
                'pct'     => $pct,
                'message' => $msg,
            ];
        };

        $file = $this->request->getFile('backup_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Please select a valid backup file (.zip or .sql).',
                'logs'    => $logs,
            ]);
        }

        $clientName = $file->getClientName();
        $ext = strtolower($file->getClientExtension());
        if ($ext !== 'sql' && $ext !== 'zip') {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Invalid file type. Only compressed .zip or .sql files are allowed.',
                'logs'    => $logs,
            ]);
        }

        $logCallback("Uploaded file `{$clientName}` verified.", 5);

        $backupDir = $this->backupService->getBackupDir();
        $timestampStr = date('Y-m-d_H-i-s');
        $safeName = 'restore_' . $timestampStr . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $clientName);
        $logFileName = 'restore_' . $timestampStr . '_' . uniqid() . '.log';
        $logFilePath = WRITEPATH . 'logs/' . $logFileName;

        $db = \Config\Database::connect();
        $userId = session()->get('user_id') ? (int) session()->get('user_id') : null;

        // Insert pending restore record
        $db->table('db_restores')->insert([
            'filename'          => $safeName,
            'original_filename' => $clientName,
            'file_size'         => $file->getSize(),
            'status'            => 'in_progress',
            'log_file'          => $logFileName,
            'restored_by'       => $userId,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $restoreId = $db->insertID();

        // Helper to save log file to disk
        $saveLogToDisk = function() use (&$logs, $logFilePath, $clientName, $timestampStr) {
            $content = "============================================================\n";
            $content .= "DATABASE RESTORE AUDIT LOG\n";
            $content .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
            $content .= "Original File: " . $clientName . "\n";
            $content .= "============================================================\n\n";
            foreach ($logs as $l) {
                $content .= sprintf("[%s] [%3d%%] %s\n", $l['time'], $l['pct'], $l['message']);
            }
            @file_put_contents($logFilePath, $content);
        };

        try {
            $file->move($backupDir, $safeName);
            $targetPath = $backupDir . $safeName;

            // Perform restore with live log callback
            $res = $this->backupService->restoreBackup($targetPath, $logCallback);
            $duration = round(microtime(true) - $startTime, 2);

            if ($res['success']) {
                $tablesCount = $res['tables_count'] ?? 0;
                $rowsCount = $res['rows_count'] ?? 0;

                // Save full log to disk
                $saveLogToDisk();

                // Update restore history
                $db->table('db_restores')->where('id', $restoreId)->update([
                    'status'           => 'completed',
                    'tables_count'     => $tablesCount,
                    'rows_count'       => $rowsCount,
                    'duration_seconds' => $duration,
                    'log_file'         => $logFileName,
                    'created_at'       => date('Y-m-d H:i:s'),
                ]);

                // Also track in db_backups so it appears in the backup pool
                $db->table('db_backups')->insert([
                    'filename'      => $safeName,
                    'file_path'     => 'backup/' . $safeName,
                    'file_size'     => file_exists($targetPath) ? filesize($targetPath) : 0,
                    'source'        => 'manual',
                    'tables_count'  => $tablesCount,
                    'rows_count'    => $rowsCount,
                    'email_status'  => 'disabled',
                    'gdrive_status' => 'disabled',
                    'created_by'    => $userId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);

                return $this->response->setJSON([
                    'success'      => true,
                    'message'      => "Database successfully restored from `{$clientName}` in {$duration}s!",
                    'tables_count' => $tablesCount,
                    'rows_count'   => $rowsCount,
                    'duration'     => $duration,
                    'log_file'     => $logFileName,
                    'logs'         => $logs,
                ]);
            }

            $errMsg = $res['error'] ?? 'Unknown restore error';
            $saveLogToDisk();

            $db->table('db_restores')->where('id', $restoreId)->update([
                'status'           => 'failed',
                'error_message'    => $errMsg,
                'log_file'         => $logFileName,
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ]);

            return $this->response->setJSON([
                'success' => false,
                'error'   => $errMsg,
                'log_file'=> $logFileName,
                'logs'    => $logs,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'uploadAndRestoreAjax error: ' . $e->getMessage());
            $logCallback("Fatal Error: " . $e->getMessage(), 100);
            $saveLogToDisk();

            $db->table('db_restores')->where('id', $restoreId)->update([
                'status'           => 'failed',
                'error_message'    => $e->getMessage(),
                'log_file'         => $logFileName,
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ]);

            return $this->response->setJSON([
                'success' => false,
                'error'   => $e->getMessage(),
                'log_file'=> $logFileName,
                'logs'    => $logs,
            ]);
        }
    }

    /**
     * Restore Database from an uploaded SQL or ZIP backup file
     */
    public function uploadAndRestore()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $file = $this->request->getFile('backup_file');
        if (!$file || !$file->isValid()) {
            return redirect()->to('settings/backup')->with('error', 'Please select a valid backup file (.zip or .sql) to upload.');
        }

        $clientName = $file->getClientName();
        $ext = strtolower($file->getClientExtension());
        if ($ext !== 'sql' && $ext !== 'zip') {
            return redirect()->to('settings/backup')->with('error', 'Invalid file type. Only compressed .zip or .sql files are allowed.');
        }

        $backupDir = $this->backupService->getBackupDir();
        $safeName = 'upload_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $clientName);

        try {
            // Move uploaded file to backup directory
            $file->move($backupDir, $safeName);
            $targetPath = $backupDir . $safeName;

            // Perform restore (DatabaseBackupService will auto-detect .zip, extract .sql and restore)
            $res = $this->backupService->restoreBackup($targetPath);
            if ($res['success']) {
                // Log in db_backups table
                $db = \Config\Database::connect();
                $tables = $db->listTables();
                $totalRows = 0;
                foreach ($tables as $t) {
                    $totalRows += (int) $db->table($t)->countAllResults();
                }

                $userId = session()->get('user_id') ? (int) session()->get('user_id') : null;
                $db->table('db_backups')->insert([
                    'filename'      => $safeName,
                    'file_path'     => 'backup/' . $safeName,
                    'file_size'     => file_exists($targetPath) ? filesize($targetPath) : 0,
                    'source'        => 'manual',
                    'tables_count'  => count($tables),
                    'rows_count'    => $totalRows,
                    'email_status'  => 'disabled',
                    'gdrive_status' => 'disabled',
                    'created_by'    => $userId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);

                return redirect()->to('settings/backup')->with('success', "Database successfully restored from `{$clientName}`!");
            }

            return redirect()->to('settings/backup')->with('error', "Restore failed: " . ($res['error'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            log_message('error', 'uploadAndRestore error: ' . $e->getMessage());
            return redirect()->to('settings/backup')->with('error', 'Restore error: ' . $e->getMessage());
        }
    }

    /**
     * Restore Database from a specific SQL backup file
     */
    public function restore(string $idOrFilename)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $resolved = $this->resolveBackupFile($idOrFilename);
        if (!$resolved) {
            return redirect()->to('settings/backup')->with('error', 'Selected backup file does not exist on disk.');
        }

        $safeName = $resolved['filename'];
        $filePath = $resolved['filepath'];

        try {
            $res = $this->backupService->restoreBackup($filePath);
            if ($res['success']) {
                return redirect()->to('settings/backup')->with('success', "Database successfully restored from `{$safeName}`!");
            }
            return redirect()->to('settings/backup')->with('error', "Restore failed: " . ($res['error'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            log_message('error', 'Restore error: ' . $e->getMessage());
            return redirect()->to('settings/backup')->with('error', 'Restore error: ' . $e->getMessage());
        }
    }

    /**
     * Send Existing Backup to Email
     */
    public function email(string $idOrFilename)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $resolved = $this->resolveBackupFile($idOrFilename);
        if (!$resolved) {
            return redirect()->to('settings/backup')->with('error', 'Backup file does not exist on disk.');
        }

        $safeName = $resolved['filename'];
        $filePath = $resolved['filepath'];

        $res = $this->backupService->sendBackupByEmail($filePath, $safeName);
        if ($res['success']) {
            $db = \Config\Database::connect();
            $db->table('db_backups')->where('filename', $safeName)->update([
                'email_status' => 'sent',
                'email_error'  => null,
            ]);
            return redirect()->to('settings/backup')->with('success', "Backup `{$safeName}` emailed successfully!");
        }

        return redirect()->to('settings/backup')->with('error', "Failed to email backup: " . ($res['error'] ?? 'Unknown'));
    }

    /**
     * Upload Existing Backup to Google Drive
     */
    public function gdrive(string $idOrFilename)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $resolved = $this->resolveBackupFile($idOrFilename);
        if (!$resolved) {
            return redirect()->to('settings/backup')->with('error', 'Backup file does not exist on disk.');
        }

        $safeName = $resolved['filename'];
        $filePath = $resolved['filepath'];

        $res = $this->backupService->uploadToGoogleDrive($filePath, $safeName);
        if ($res['success']) {
            $db = \Config\Database::connect();
            $db->table('db_backups')->where('filename', $safeName)->update([
                'gdrive_status'  => 'uploaded',
                'gdrive_file_id' => $res['file_id'] ?? null,
                'gdrive_error'   => null,
            ]);
            return redirect()->to('settings/backup')->with('success', "Uploaded `{$safeName}` to Google Drive (ID: {$res['file_id']})!");
        }

        return redirect()->to('settings/backup')->with('error', "Google Drive upload failed: " . ($res['error'] ?? 'Unknown'));
    }

    /**
     * Delete a Backup File
     */
    public function delete(string $idOrFilename)
    {
        $backupDir = $this->backupService->getBackupDir();
        $safeName = null;
        $db = \Config\Database::connect();

        if (ctype_digit($idOrFilename)) {
            $backupRow = $db->table('db_backups')->where('id', (int) $idOrFilename)->get()->getRowArray();
            if ($backupRow && !empty($backupRow['filename'])) {
                $safeName = basename($backupRow['filename']);
            }
        }

        if (!$safeName) {
            $safeName = basename($idOrFilename);
        }

        $filePath = $backupDir . $safeName;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $db->table('db_backups')->where('filename', $safeName)->delete();
        if (ctype_digit($idOrFilename)) {
            $db->table('db_backups')->where('id', (int) $idOrFilename)->delete();
        }

        return redirect()->to('settings/backup')->with('success', "Backup `{$safeName}` deleted successfully.");
    }

    /**
     * Update Backup & Cloud Configuration
     */
    public function updateConfig()
    {
        $db = \Config\Database::connect();
        $post = $this->request->getPost();
        $section = $post['section'] ?? '';

        // Define fields managed by each section
        $sectionFields = [
            'auto_backup' => [
                'backup_auto_enabled',
                'backup_cron_time',
                'backup_retention_days',
            ],
            'email' => [
                'backup_email_enabled',
                'backup_email_recipients',
                'backup_email_smtp_host',
                'backup_email_smtp_port',
                'backup_email_smtp_user',
                'backup_email_smtp_pass',
                'backup_email_smtp_crypto',
                'backup_email_from_email',
                'backup_email_from_name',
            ],
            'gdrive' => [
                'backup_gdrive_enabled',
                'backup_gdrive_client_id',
                'backup_gdrive_client_secret',
                'backup_gdrive_refresh_token',
                'backup_gdrive_folder_id',
            ],
        ];

        // If section is specified, only update fields belonging to that section
        // Otherwise, update whatever valid keys were posted
        if (isset($sectionFields[$section])) {
            $keysToUpdate = $sectionFields[$section];
        } else {
            // Fallback: all possible keys
            $keysToUpdate = array_merge(...array_values($sectionFields));
        }

        foreach ($keysToUpdate as $key) {
            // For checkboxes (e.g. enabled switches), if absent in the specific section's post, treat as '0'
            $val = $post[$key] ?? '0';
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $val = trim((string) $val);

            $exists = $db->table('settings')->where('setting_key', $key)->countAllResults();
            if ($exists > 0) {
                $db->table('settings')->where('setting_key', $key)->update([
                    'setting_value' => $val,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            } else {
                $db->table('settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $val,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to('settings/backup')->with('success', 'Backup configuration updated successfully.');
    }
}

