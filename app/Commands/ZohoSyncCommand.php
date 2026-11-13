<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\Zoho\ZohoSyncOrchestrator;

class ZohoSyncCommand extends BaseCommand
{
    protected $group       = 'Zoho';
    protected $name        = 'zoho:sync';
    protected $description = 'Run Zoho Books synchronization (to_zoho, from_zoho, both, queue worker, or retry failed).';
    protected $usage       = 'zoho:sync [options]';
    protected $options     = [
        '--direction'    => 'Sync direction: to_zoho | from_zoho | both | queue (default: queue)',
        '--module'       => 'Optional module key (e.g. customers, vendors, items, invoices, bills)',
        '--retry-failed' => 'Retry failed sync mappings & queue records',
        '--clean-logs'   => 'Prune logs and payload files older than specified days (default: 90)',
        '--limit'        => 'Batch limit for queue processing (default: 50)',
    ];

    public function run(array $params)
    {
        $orchestrator = new ZohoSyncOrchestrator();

        $cleanLogs   = CLI::getOption('clean-logs') !== null || in_array('--clean-logs', $params, true);
        $retryFailed = CLI::getOption('retry-failed') !== null || in_array('--retry-failed', $params, true);
        $direction   = (string) (CLI::getOption('direction') ?: 'queue');
        $module      = CLI::getOption('module') ? trim((string) CLI::getOption('module')) : null;
        $limit       = max(1, (int) (CLI::getOption('limit') ?: 50));

        CLI::write('=== Zoho Books Synchronization CLI ===', 'cyan');

        try {
            if ($cleanLogs) {
                $days = max(7, (int) (CLI::getOption('clean-logs') ?: 90));
                CLI::write("Pruning Zoho API logs and payload files older than {$days} days...", 'yellow');
                $deleted = (new \App\Models\Zoho\ZohoSyncLogModel())->pruneLogsOlderThan($days);
                CLI::write("Cleaned {$deleted} log records and expired JSON files.", 'green');
                return EXIT_SUCCESS;
            }

            if ($retryFailed) {
                CLI::write('Retrying failed Zoho sync items...', 'yellow');
                $result = $orchestrator->retryFailedSync();
            } else {
                switch (strtolower($direction)) {
                    case 'from_zoho':
                    case 'pull':
                        CLI::write('Pulling changes from Zoho Books' . ($module ? " [module: {$module}]" : ' [all modules]') . '...', 'yellow');
                        $result = $orchestrator->syncFromZoho($module);
                        break;

                    case 'to_zoho':
                    case 'push':
                        CLI::write('Pushing local changes to Zoho Books' . ($module ? " [module: {$module}]" : ' [all modules]') . '...', 'yellow');
                        $result = $orchestrator->syncToZoho($module);
                        break;

                    case 'both':
                    case 'bidirectional':
                    case 'full':
                        CLI::write('Running full bidirectional synchronization' . ($module ? " [module: {$module}]" : ' [all modules]') . '...', 'yellow');
                        $result = $orchestrator->fullBidirectionalSync($module);
                        break;

                    case 'queue':
                    default:
                        CLI::write("Processing pending Zoho sync queue (limit={$limit})...", 'yellow');
                        $result = $orchestrator->processPendingQueue($limit);
                        break;
                }
            }

            CLI::write('Result Status : ' . ($result['status'] ?? 'completed'), 'green');
            CLI::write(sprintf(
                'Processed: %d | Created: %d | Updated: %d | Deleted/Voided: %d | Skipped: %d | Conflicts: %d | Failed: %d',
                (int) ($result['total_records'] ?? $result['processed'] ?? 0),
                (int) ($result['created_count'] ?? 0),
                (int) ($result['updated_count'] ?? 0),
                (int) ($result['deleted_count'] ?? 0),
                (int) ($result['skipped_count'] ?? 0),
                (int) ($result['conflict_count'] ?? 0),
                (int) ($result['failed_count'] ?? $result['failed'] ?? 0)
            ), 'light_gray');

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $err) {
                    CLI::error(' - ' . $err);
                }
            }
        } catch (\Throwable $e) {
            CLI::error('Zoho Sync CLI Error: ' . $e->getMessage());
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}

