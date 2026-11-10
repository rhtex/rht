<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\DatabaseBackupService;

class DatabaseBackupCommand extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:backup';
    protected $description = 'Generate full database backup SQL file, with automatic email and Google Drive upload.';
    protected $usage       = 'db:backup [options]';
    protected $options     = [
        '--cron'  => 'Flag indicating command is running from automated midnight cron scheduler',
        '--email' => 'Explicitly trigger email dispatch',
        '--gdrive'=> 'Explicitly trigger Google Drive upload',
    ];

    public function run(array $params)
    {
        CLI::write("=== Database Backup Engine ===", 'cyan');
        CLI::write("Started at: " . date('Y-m-d H:i:s'), 'white');

        $isCron = CLI::getOption('cron') !== null || in_array('--cron', $params, true);
        $source = $isCron ? 'auto_cron' : 'manual';

        try {
            $service = new DatabaseBackupService();
            $res = $service->createBackup($source, null);

            CLI::write("Backup successfully created!", 'green');
            CLI::write("Filename: " . $res['filename'], 'yellow');
            CLI::write("Size: " . round($res['file_size'] / (1024 * 1024), 2) . " MB", 'yellow');
            CLI::write("Tables: " . $res['tables_count'] . " | Total Rows: " . number_format($res['rows_count']), 'yellow');

            if (!empty($res['email'])) {
                if ($res['email']['success']) {
                    CLI::write("Email sent successfully.", 'green');
                } else {
                    CLI::write("Email dispatch failed: " . ($res['email']['error'] ?? 'Unknown'), 'red');
                }
            }

            if (!empty($res['gdrive'])) {
                if ($res['gdrive']['success']) {
                    CLI::write("Google Drive upload successful! File ID: " . ($res['gdrive']['file_id'] ?? ''), 'green');
                } else {
                    CLI::write("Google Drive upload failed: " . ($res['gdrive']['error'] ?? 'Unknown'), 'red');
                }
            }

            CLI::write("=== Backup Completed ===", 'cyan');
        } catch (\Throwable $e) {
            CLI::error("Backup failed: " . $e->getMessage());
            log_message('error', 'DatabaseBackupCommand failed: ' . $e->getMessage());
        }
    }
}

