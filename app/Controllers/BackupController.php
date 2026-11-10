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

        // 2. Fetch all physical .sql files in backup/ folder (including external uploads/untracked files)
        $scannedFiles = [];
        if (is_dir($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $f) {
                if ($f !== '.' && $f !== '..' && str_ends_with(strtolower($f), '.sql')) {
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
     * Download SQL Backup File
     */
    public function download(string $filename)
    {
        $safeName = basename($filename);
        $filePath = $this->backupService->getBackupDir() . $safeName;

        if (!file_exists($filePath)) {
            return redirect()->to('settings/backup')->with('error', 'File not found on disk.');
        }

        return $this->response->download($filePath, null)->setFileName($safeName);
    }

    /**
     * Restore Database from a specific SQL backup file
     */
    public function restore(string $filename)
    {
        $safeName = basename($filename);
        $filePath = $this->backupService->getBackupDir() . $safeName;

        if (!file_exists($filePath)) {
            return redirect()->to('settings/backup')->with('error', 'Selected backup file does not exist.');
        }

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
    public function email(string $filename)
    {
        $safeName = basename($filename);
        $filePath = $this->backupService->getBackupDir() . $safeName;

        if (!file_exists($filePath)) {
            return redirect()->to('settings/backup')->with('error', 'Backup file does not exist on disk.');
        }

        $res = $this->backupService->sendBackupByEmail($filePath, $safeName);
        if ($res['success']) {
            return redirect()->to('settings/backup')->with('success', "Backup `{$safeName}` emailed successfully!");
        }

        return redirect()->to('settings/backup')->with('error', "Failed to email backup: " . ($res['error'] ?? 'Unknown'));
    }

    /**
     * Upload Existing Backup to Google Drive
     */
    public function gdrive(string $filename)
    {
        $safeName = basename($filename);
        $filePath = $this->backupService->getBackupDir() . $safeName;

        if (!file_exists($filePath)) {
            return redirect()->to('settings/backup')->with('error', 'Backup file does not exist on disk.');
        }

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
    public function delete(string $filename)
    {
        $safeName = basename($filename);
        $filePath = $this->backupService->getBackupDir() . $safeName;

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $db = \Config\Database::connect();
        $db->table('db_backups')->where('filename', $safeName)->delete();

        return redirect()->to('settings/backup')->with('success', "Backup `{$safeName}` deleted successfully.");
    }

    /**
     * Update Backup & Cloud Configuration
     */
    public function updateConfig()
    {
        $db = \Config\Database::connect();
        $post = $this->request->getPost();

        $keys = [
            'backup_auto_enabled',
            'backup_cron_time',
            'backup_email_enabled',
            'backup_email_recipients',
            'backup_email_smtp_host',
            'backup_email_smtp_port',
            'backup_email_smtp_user',
            'backup_email_smtp_pass',
            'backup_email_smtp_crypto',
            'backup_email_from_email',
            'backup_email_from_name',
            'backup_gdrive_enabled',
            'backup_gdrive_client_id',
            'backup_gdrive_client_secret',
            'backup_gdrive_refresh_token',
            'backup_gdrive_folder_id',
        ];

        foreach ($keys as $key) {
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

        return redirect()->to('settings/backup')->with('success', 'Backup & Cloud configurations updated successfully.');
    }
}

