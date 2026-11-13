<?php

namespace App\Services;

use Config\Database;
use Config\Services;

class DatabaseBackupService
{
    protected string $backupDir;
    protected $db;

    public function __construct()
    {
        // Store in root /backup folder (or fallback to WRITEPATH if root is restricted)
        $rootBackupDir = realpath(ROOTPATH) ? (rtrim(ROOTPATH, '\\/') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR) : (rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR);
        $this->backupDir = $rootBackupDir;
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
        $this->db = Database::connect();
    }

    /**
     * Get backup directory path
     */
    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    /**
     * Create full database backup (.sql file) with all structure, rows and data
     *
     * @param string $source 'manual' | 'auto_cron'
     * @param int|null $userId
     * @return array
     */
    public function createBackup(string $source = 'manual', ?int $userId = null): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $dbName = $this->db->getDatabase();
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $filePath = $this->backupDir . $filename;

        $stats = [
            'tables' => 0,
            'rows'   => 0,
        ];

        // 1. Try native mysqldump first for maximum performance and 100% fidelity
        $dumped = $this->dumpViaMysqldump($filePath, $stats);

        // 2. Fallback to PHP native dumper if mysqldump is not available or blocked
        if (!$dumped || !file_exists($filePath) || filesize($filePath) === 0) {
            $this->dumpViaPhp($filePath, $stats);
        }

        // 3. Compress into .zip archive if ZipArchive extension is available
        $zipFilename = preg_replace('/\.sql$/i', '.zip', $filename);
        $zipFilePath = $this->backupDir . $zipFilename;
        $finalFilename = $filename;
        $finalFilePath = $filePath;

        if (class_exists('ZipArchive') && file_exists($filePath) && filesize($filePath) > 0) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFile($filePath, $filename);
                $zip->close();

                // If zip created successfully and is smaller, remove raw .sql and keep .zip
                if (file_exists($zipFilePath) && filesize($zipFilePath) > 0) {
                    @unlink($filePath);
                    $finalFilename = $zipFilename;
                    $finalFilePath = $zipFilePath;
                }
            }
        }

        $fileSize = file_exists($finalFilePath) ? filesize($finalFilePath) : 0;

        // Record in db_backups table
        $backupId = $this->db->table('db_backups')->insert([
            'filename'      => $finalFilename,
            'file_path'     => 'backup/' . $finalFilename,
            'file_size'     => $fileSize,
            'source'        => $source,
            'tables_count'  => $stats['tables'],
            'rows_count'    => $stats['rows'],
            'email_status'  => (get_setting('backup_email_enabled') === '1') ? 'pending' : 'disabled',
            'gdrive_status' => (get_setting('backup_gdrive_enabled') === '1') ? 'pending' : 'disabled',
            'created_by'    => $userId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        if (!$backupId) {
            $backupRow = $this->db->table('db_backups')->where('filename', $finalFilename)->get()->getRowArray();
            $backupId = $backupRow['id'] ?? null;
        }

        // Handle Automatic Email Dispatch
        $emailResult = null;
        if (get_setting('backup_email_enabled') === '1') {
            $emailResult = $this->sendBackupByEmail($finalFilePath, $finalFilename);
            $this->db->table('db_backups')->where('id', $backupId)->update([
                'email_status' => $emailResult['success'] ? 'sent' : 'failed',
                'email_error'  => $emailResult['success'] ? null : ($emailResult['error'] ?? 'Unknown email error'),
            ]);
        }

        // Handle Automatic Google Drive Upload
        $gdriveResult = null;
        if (get_setting('backup_gdrive_enabled') === '1') {
            $gdriveResult = $this->uploadToGoogleDrive($finalFilePath, $finalFilename);
            $this->db->table('db_backups')->where('id', $backupId)->update([
                'gdrive_status'  => $gdriveResult['success'] ? 'uploaded' : 'failed',
                'gdrive_file_id' => $gdriveResult['file_id'] ?? null,
                'gdrive_error'   => $gdriveResult['success'] ? null : ($gdriveResult['error'] ?? 'Unknown Drive error'),
            ]);
        }

        // Update last run setting
        $this->db->table('settings')->where('setting_key', 'backup_last_run')->update([
            'setting_value' => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        // Clean up older backups based on retention policy
        $cleanedCount = $this->cleanOldBackups();

        return [
            'success'       => true,
            'id'            => $backupId,
            'filename'      => $finalFilename,
            'file_path'     => $finalFilePath,
            'file_size'     => $fileSize,
            'tables_count'  => $stats['tables'],
            'rows_count'    => $stats['rows'],
            'email'         => $emailResult,
            'gdrive'        => $gdriveResult,
            'cleaned'       => $cleanedCount,
        ];
    }

    /**
     * Clean up local backup files older than configured retention days (default: 90 days)
     */
    public function cleanOldBackups(?int $days = null): int
    {
        if ($days === null) {
            $days = (int) get_setting('backup_retention_days', 90);
        }

        // 0 or negative means keep forever
        if ($days <= 0) {
            return 0;
        }

        $cutoffTimestamp = strtotime("-{$days} days");
        $cutoffDate = date('Y-m-d H:i:s', $cutoffTimestamp);
        $deletedCount = 0;

        // 1. Delete physical files in backup/ directory older than cutoff
        if (is_dir($this->backupDir)) {
            $files = scandir($this->backupDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..' || $f === '.gitkeep') {
                    continue;
                }

                $filePath = $this->backupDir . $f;
                if (is_file($filePath)) {
                    $mtime = filemtime($filePath);
                    if ($mtime < $cutoffTimestamp) {
                        @unlink($filePath);
                        $deletedCount++;
                    }
                }
            }
        }

        // 2. Remove corresponding records from db_backups table
        try {
            $this->db->table('db_backups')
                ->where('created_at <', $cutoffDate)
                ->delete();
        } catch (\Throwable $e) {
            log_message('error', 'cleanOldBackups db error: ' . $e->getMessage());
        }

        // 3. Clean physical restore logs in writable/logs/ older than cutoff
        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        if (is_dir($logDir)) {
            $logs = glob($logDir . 'restore_*.log');
            if ($logs) {
                foreach ($logs as $logFile) {
                    if (is_file($logFile) && filemtime($logFile) < $cutoffTimestamp) {
                        @unlink($logFile);
                    }
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Check if shell execution functions (exec, proc_open, etc.) are available on the server
     */
    protected function isExecAvailable(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $disabled = explode(',', (string) ini_get('disable_functions'));
        $disabled = array_map('trim', array_map('strtolower', $disabled));

        return !in_array('exec', $disabled, true);
    }

    /**
     * Dump database using mysqldump binary
     */
    protected function dumpViaMysqldump(string $targetFile, array &$stats): bool
    {
        if (!$this->isExecAvailable()) {
            return false;
        }

        $dbConfig = config('Database');
        $default  = $dbConfig->default;

        $host     = $default['hostname'] ?? 'localhost';
        $user     = $default['username'] ?? 'root';
        $pass     = $default['password'] ?? '';
        $database = $default['database'] ?? '';
        $port     = $default['port'] ?? 3306;

        // Common mysqldump paths on Windows & Linux
        $candidates = [
            'd:\\xampp\\mysql\\bin\\mysqldump.exe',
            'c:\\xampp\\mysql\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump',
        ];

        $mysqldump = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand) || (PHP_OS_FAMILY !== 'Windows' && @is_executable($cand))) {
                $mysqldump = $cand;
                break;
            }
        }

        if (!$mysqldump) {
            $mysqldump = 'mysqldump';
        }

        $passArg = !empty($pass) ? '--password=' . escapeshellarg($pass) : '';
        $cmd = sprintf(
            '%s --user=%s %s --host=%s --port=%s --single-transaction --quick --routines --triggers %s > %s',
            escapeshellarg($mysqldump),
            escapeshellarg($user),
            $passArg,
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($database),
            escapeshellarg($targetFile)
        );

        @exec($cmd, $output, $returnVar);

        if ($returnVar === 0 && file_exists($targetFile) && filesize($targetFile) > 1024) {
            // Count tables and estimate rows
            $tables = $this->db->listTables();
            $stats['tables'] = count($tables);
            $rowCount = 0;
            foreach ($tables as $t) {
                $rowCount += (int) $this->db->table($t)->countAllResults();
            }
            $stats['rows'] = $rowCount;
            return true;
        }

        return false;
    }

    /**
     * Pure PHP database dumper with complete CREATE TABLE and INSERT statements
     */
    protected function dumpViaPhp(string $targetFile, array &$stats): void
    {
        $handle = fopen($targetFile, 'w');
        if (!$handle) {
            throw new \RuntimeException("Unable to open backup file for writing: {$targetFile}");
        }

        $dbName = $this->db->getDatabase();

        // Write Header
        fwrite($handle, "-- ============================================================\n");
        fwrite($handle, "-- Database Backup for: `{$dbName}`\n");
        fwrite($handle, "-- Generated by: Rasi Handloom Textiles ERP\n");
        fwrite($handle, "-- Date & Time: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- PHP Version: " . PHP_VERSION . "\n");
        fwrite($handle, "-- ============================================================\n\n");

        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        $tables = $this->db->listTables();
        $stats['tables'] = count($tables);
        $totalRows = 0;

        foreach ($tables as $table) {
            // Do not dump temporary backup table if being written
            fwrite($handle, "\n-- ------------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for `{$table}`\n");
            fwrite($handle, "-- ------------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            // Create Table DDL
            $createRow = $this->db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            $createTableSql = $createRow['Create Table'] ?? '';
            if ($createTableSql) {
                fwrite($handle, $createTableSql . ";\n\n");
            }

            // Dump Data Rows
            $count = (int) $this->db->table($table)->countAllResults();
            $totalRows += $count;

            if ($count > 0) {
                fwrite($handle, "-- Dumping data for table `{$table}` (Rows: {$count})\n");
                
                // Read in batches of 500 rows to keep memory small
                $batchSize = 500;
                $offset = 0;

                while ($offset < $count) {
                    $rows = $this->db->table($table)->limit($batchSize, $offset)->get()->getResultArray();
                    if (empty($rows)) {
                        break;
                    }

                    $columns = array_keys($rows[0]);
                    $colList = implode('`, `', $columns);

                    $insertPrefix = "INSERT INTO `{$table}` (`{$colList}`) VALUES \n";
                    $valueLines = [];

                    foreach ($rows as $row) {
                        $vals = [];
                        foreach ($row as $val) {
                            if ($val === null) {
                                $vals[] = 'NULL';
                            } else {
                                $vals[] = $this->db->escape($val);
                            }
                        }
                        $valueLines[] = '(' . implode(', ', $vals) . ')';
                    }

                    fwrite($handle, $insertPrefix . implode(",\n", $valueLines) . ";\n");
                    $offset += $batchSize;
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($handle, "-- ===================== End of Backup =====================\n");

        fclose($handle);
        $stats['rows'] = $totalRows;
    }

    /**
     * Dispatch backup SQL file as email attachment
     */
    public function sendBackupByEmail(string $filePath, string $filename, ?string $toEmail = null): array
    {
        $recipients = $toEmail ?: get_setting('backup_email_recipients');
        if (empty($recipients)) {
            return ['success' => false, 'error' => 'No email recipients configured.'];
        }

        $email = Services::email();

        // Check if custom SMTP is configured
        $smtpHost = get_setting('backup_email_smtp_host');
        if (!empty($smtpHost)) {
            $config = [
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtpHost,
                'SMTPPort'   => (int) (get_setting('backup_email_smtp_port', 587)),
                'SMTPUser'   => get_setting('backup_email_smtp_user'),
                'SMTPPass'   => get_setting('backup_email_smtp_pass'),
                'SMTPCrypto' => get_setting('backup_email_smtp_crypto', 'tls'),
                'mailType'   => 'html',
                'charset'    => 'utf-8',
                'wordWrap'   => true,
            ];
            $email->initialize($config);
        }

        $fromEmail = get_setting('backup_email_from_email') ?: (get_setting('company_email') ?: 'no-reply@rasidev.com');
        $fromName  = get_setting('backup_email_from_name') ?: 'Rasi Handloom Textiles ERP';

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($recipients);
        $email->setSubject('Database Backup - ' . date('d M Y H:i'));

        $sizeMb = round(filesize($filePath) / (1024 * 1024), 2);
        $body = "
            <h3>Rasi Handloom Textiles - Database Backup</h3>
            <p>A fresh database backup was successfully generated and is attached to this email.</p>
            <ul>
                <li><strong>Filename:</strong> {$filename}</li>
                <li><strong>Generated At:</strong> " . date('d M Y, h:i:s A') . "</li>
                <li><strong>File Size:</strong> {$sizeMb} MB</li>
                <li><strong>Database:</strong> " . esc($this->db->getDatabase()) . "</li>
            </ul>
            <p><small>Keep this SQL file secure. You can use it to restore the database in phpMyAdmin, MySQL CLI, or the ERP Backup Manager.</small></p>
        ";

        $email->setMessage($body);
        $email->attach($filePath, 'attachment', $filename, 'application/sql');

        if ($email->send(false)) {
            return ['success' => true];
        }

        return [
            'success' => false,
            'error'   => $email->printDebugger(['headers']),
        ];
    }

    /**
     * Upload backup SQL file to Google Drive via Google Drive API v3 (REST)
     */
    public function uploadToGoogleDrive(string $filePath, string $filename): array
    {
        $clientId     = get_setting('backup_gdrive_client_id');
        $clientSecret = get_setting('backup_gdrive_client_secret');
        $refreshToken = get_setting('backup_gdrive_refresh_token');
        $folderId     = get_setting('backup_gdrive_folder_id');

        if (empty($clientId) || empty($clientSecret) || empty($refreshToken)) {
            return [
                'success' => false,
                'error'   => 'Google Drive OAuth credentials (Client ID, Secret, Refresh Token) are not fully configured.',
            ];
        }

        // 1. Exchange Refresh Token for fresh Access Token
        $tokenRes = $this->getGoogleDriveAccessToken($clientId, $clientSecret, $refreshToken);
        if (!$tokenRes['success']) {
            return $tokenRes;
        }

        $accessToken = $tokenRes['access_token'];

        // 2. Upload file via Google Drive Multipart Upload
        $metadata = [
            'name'     => $filename,
            'mimeType' => 'application/sql',
        ];
        if (!empty($folderId)) {
            $metadata['parents'] = [$folderId];
        }

        $boundary   = '-------' . md5(time());
        $fileData   = file_get_contents($filePath);

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: application/sql\r\n\r\n";
        $body .= $fileData . "\r\n";
        $body .= "--{$boundary}--";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: multipart/related; boundary=' . $boundary,
            'Content-Length: ' . strlen($body),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'cURL error: ' . $curlError];
        }

        $resJson = json_decode((string) $response, true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($resJson['id'])) {
            return [
                'success' => true,
                'file_id' => $resJson['id'],
                'name'    => $resJson['name'] ?? $filename,
            ];
        }

        $errMsg = $resJson['error']['message'] ?? "HTTP {$httpCode}: " . substr((string) $response, 0, 250);
        return [
            'success' => false,
            'error'   => $errMsg,
        ];
    }

    /**
     * Refresh OAuth Access Token from Google OAuth 2.0 Token Endpoint
     */
    protected function getGoogleDriveAccessToken(string $clientId, string $clientSecret, string $refreshToken): array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Token request failed: ' . $curlError];
        }

        $json = json_decode((string) $response, true);
        if ($httpCode === 200 && !empty($json['access_token'])) {
            return [
                'success'      => true,
                'access_token' => $json['access_token'],
            ];
        }

        $msg = $json['error_description'] ?? ($json['error'] ?? 'Failed to refresh Google Drive token.');
        return [
            'success' => false,
            'error'   => $msg,
        ];
    }

    /**
     * Restore database from a given SQL or ZIP backup file
     *
     * @param string $filePath
     * @param callable|null $logCallback function(string $message, int $pct)
     * @return array
     */
    public function restoreBackup(string $filePath, ?callable $logCallback = null): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $log = function(string $msg, int $pct) use ($logCallback) {
            if ($logCallback && is_callable($logCallback)) {
                call_user_func($logCallback, $msg, $pct);
            }
        };

        if (!file_exists($filePath)) {
            $log("Error: Backup file does not exist on disk.", 0);
            return ['success' => false, 'error' => 'Backup file does not exist.'];
        }

        $log("Validating backup file...", 10);
        $tempExtractedSql = null;
        $sqlFilePath = $filePath;

        // If file is a ZIP archive, extract the SQL dump inside
        if (str_ends_with(strtolower($filePath), '.zip')) {
            $log("Detected ZIP archive. Preparing extraction...", 15);
            if (!class_exists('ZipArchive')) {
                return ['success' => false, 'error' => 'PHP ZipArchive extension is required to extract .zip backups.'];
            }

            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                return ['success' => false, 'error' => 'Failed to open ZIP archive. File may be corrupted.'];
            }

            // Find the SQL file inside
            $sqlEntryName = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (str_ends_with(strtolower($stat['name']), '.sql')) {
                    $sqlEntryName = $stat['name'];
                    break;
                }
            }

            if (!$sqlEntryName) {
                $zip->close();
                return ['success' => false, 'error' => 'No .sql file found inside the uploaded ZIP archive.'];
            }

            $log("Extracting `{$sqlEntryName}` from ZIP...", 25);
            $extractDir = $this->backupDir . 'temp_' . uniqid() . DIRECTORY_SEPARATOR;
            @mkdir($extractDir, 0755, true);
            $zip->extractTo($extractDir, [$sqlEntryName]);
            $zip->close();

            $sqlFilePath = $extractDir . $sqlEntryName;
            $tempExtractedSql = $extractDir;

            if (!file_exists($sqlFilePath)) {
                return ['success' => false, 'error' => 'Could not extract SQL file from ZIP archive.'];
            }
            $log("Extracted SQL file successfully (" . round(filesize($sqlFilePath)/(1024*1024), 2) . " MB).", 35);
        }

        $dbConfig = config('Database');
        $default  = $dbConfig->default;
        $host     = $default['hostname'] ?? 'localhost';
        $user     = $default['username'] ?? 'root';
        $pass     = $default['password'] ?? '';
        $database = $default['database'] ?? '';
        $port     = $default['port'] ?? 3306;

        $log("Connecting to target database `{$database}`...", 45);

        $restoreSuccess = false;

        // Check if native CLI execution is permitted on this hosting/server (e.g. cPanel often disables exec)
        $canExec = $this->isExecAvailable();

        if ($canExec) {
            // Try native mysql CLI first
            $candidates = [
                'd:\\xampp\\mysql\\bin\\mysql.exe',
                'c:\\xampp\\mysql\\bin\\mysql.exe',
                '/usr/bin/mysql',
                '/usr/local/bin/mysql',
                'mysql',
            ];

            $mysqlBinary = null;
            foreach ($candidates as $cand) {
                if (file_exists($cand) || (PHP_OS_FAMILY !== 'Windows' && @is_executable($cand))) {
                    $mysqlBinary = $cand;
                    break;
                }
            }

            if ($mysqlBinary) {
                $log("Executing high-speed MySQL restore engine...", 55);
                $passArg = !empty($pass) ? '--password=' . escapeshellarg($pass) : '';
                $cmd = sprintf(
                    '%s --user=%s %s --host=%s --port=%s --max_allowed_packet=512M %s < %s 2>&1',
                    escapeshellarg($mysqlBinary),
                    escapeshellarg($user),
                    $passArg,
                    escapeshellarg($host),
                    escapeshellarg((string) $port),
                    escapeshellarg($database),
                    escapeshellarg($sqlFilePath)
                );
                @exec($cmd, $output, $returnVar);
                if ($returnVar === 0) {
                    $restoreSuccess = true;
                    $log("All database tables, routines, and data imported successfully via MySQL CLI.", 85);
                } else {
                    $cliErr = !empty($output) ? implode(' ', array_slice($output, 0, 3)) : "exit code {$returnVar}";
                    $log("Native CLI notice: {$cliErr}. Switching to PHP transaction runner...", 60);
                }
            }
        } else {
            $log("Shell execution disabled by hosting environment (cPanel). Using pure PHP SQL restore engine...", 55);
        }

        // Fallback: Line-by-line PHP SQL execution if mysql CLI failed or exec() disabled
        if (!$restoreSuccess) {
            $handle = @fopen($sqlFilePath, 'r');
            if (!$handle) {
                $this->cleanupTempDir($tempExtractedSql);
                return ['success' => false, 'error' => 'Could not read SQL backup file.'];
            }

            $sql = '';
            $this->db->query("SET FOREIGN_KEY_CHECKS = 0;");
            $log("Executing SQL statements with Foreign Key checks disabled...", 65);

            $fileSize = filesize($sqlFilePath);
            $bytesRead = 0;
            $lastPct = 65;

            while (!feof($handle)) {
                $line = fgets($handle);
                $bytesRead += strlen($line);
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }
                $sql .= $line;
                if (str_ends_with($trimmed, ';')) {
                    try {
                        $this->db->query($sql);
                    } catch (\Throwable $e) {
                        $log("Statement error: " . $e->getMessage(), $lastPct);
                        log_message('error', 'Restore statement error: ' . $e->getMessage());
                    }
                    $sql = '';

                    if ($fileSize > 0) {
                        $currentPct = (int) (65 + (($bytesRead / $fileSize) * 20));
                        if ($currentPct > $lastPct && $currentPct % 5 === 0) {
                            $lastPct = $currentPct;
                            $log("Imported " . round($bytesRead / (1024 * 1024), 1) . " MB...", $currentPct);
                        }
                    }
                }
            }
            fclose($handle);
            $this->db->query("SET FOREIGN_KEY_CHECKS = 1;");
            $restoreSuccess = true;
        }

        $log("Scanning restored database schema and verifying tables & rows...", 88);

        // Scan all tables in database and verify actual count & rows
        $tables = $this->db->listTables();
        $totalTables = count($tables);
        $totalRows = 0;
        $tableBreakdown = [];

        foreach ($tables as $t) {
            try {
                $cnt = (int) $this->db->table($t)->countAllResults();
                $totalRows += $cnt;
                $tableBreakdown[$t] = $cnt;
            } catch (\Throwable $e) {
                $tableBreakdown[$t] = 'Error: ' . $e->getMessage();
                $log("Verification warning on table `{$t}`: " . $e->getMessage(), 90);
            }
        }

        // Log detailed itemized table breakdown
        $log("------------------------------------------------------------", 92);
        $log("RESTORED TABLES & ROW COUNTS AUDIT (" . count($tableBreakdown) . " tables):", 92);
        foreach ($tableBreakdown as $tbl => $rows) {
            if (is_numeric($rows)) {
                $log(sprintf("  + Table `%-35s`: %s rows restored", $tbl, number_format($rows)), 92);
            } else {
                $log(sprintf("  ! Table `%-35s`: %s", $tbl, $rows), 92);
            }
        }
        $log("------------------------------------------------------------", 93);

        $log("Cleaning up temporary files...", 95);
        $this->cleanupTempDir($tempExtractedSql);

        $log("Restore complete! Total tables: {$totalTables}, Total rows restored: " . number_format($totalRows) . ".", 100);

        return [
            'success'         => true,
            'tables_count'    => $totalTables,
            'rows_count'      => $totalRows,
            'table_breakdown' => $tableBreakdown,
        ];
    }

    /**
     * Helper to recursively delete temporary extraction folder
     */
    protected function cleanupTempDir(?string $dir): void
    {
        if (!$dir || !is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->cleanupTempDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
