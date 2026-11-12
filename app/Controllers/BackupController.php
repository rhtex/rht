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
        ini_set('memory_limit', '512M');

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
        ini_set('memory_limit', '512M');

        $resolved = $this->resolveBackupFile($idOrFilename);
        if (!$resolved) {
            return redirect()->to('settings/backup')->with('error', 'File not found on disk.');
        }

        return $this->response->download($resolved['filepath'], null)->setFileName($resolved['filename']);
    }

    /**
     * Restore Database from an uploaded SQL or ZIP backup file
     */
    public function uploadAndRestore()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

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
        ini_set('memory_limit', '512M');

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
        ini_set('memory_limit', '512M');

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
        ini_set('memory_limit', '512M');

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

