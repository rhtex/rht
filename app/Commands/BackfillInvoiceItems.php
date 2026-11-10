<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\InvoiceModel;
use App\Models\Zoho\ZohoSyncMappingModel;
use App\Services\Zoho\Modules\InvoiceSyncService;

class BackfillInvoiceItems extends BaseCommand
{
    protected $group       = 'Zoho';
    protected $name        = 'zoho:backfill-items';
    protected $description = 'Backfills line items and details for older invoices from Zoho Books.';
    protected $usage       = 'zoho:backfill-items [options]';
    protected $options     = [
        '--limit' => 'Number of invoices to backfill (default: 50, use 0 for all)',
        '--id'    => 'Specific local invoice ID to backfill',
        '--sleep' => 'Milliseconds to sleep between requests to respect rate limits (default: 200)',
    ];

    public function run(array $params)
    {
        CLI::write('=== Backfill Invoice Items from Zoho Books ===', 'cyan');

        $db = \Config\Database::connect();
        $invModel   = new InvoiceModel();
        $mapping    = new ZohoSyncMappingModel();
        $invSync    = new InvoiceSyncService();

        $singleId = CLI::getOption('id');
        $limit    = CLI::getOption('limit') !== null ? (int) CLI::getOption('limit') : 50;
        $sleepMs  = CLI::getOption('sleep') !== null ? max(0, (int) CLI::getOption('sleep')) : 200;

        if ($singleId) {
            CLI::write("Fetching invoice ID #{$singleId} from Zoho Books...", 'yellow');
            $res = $invSync->pullSingleInvoiceFromZoho((int) $singleId);
            if (!empty($res['success'])) {
                $inv = $invModel->getInvoiceById((int) $singleId);
                $itemCount = count($inv['items'] ?? []);
                CLI::write("Success! Invoice #{$singleId} updated with {$itemCount} item(s).", 'green');
                return EXIT_SUCCESS;
            } else {
                CLI::error("Failed: " . ($res['message'] ?? 'Unknown error'));
                return EXIT_ERROR;
            }
        }

        // Find invoices that have NO items in invoice_items but have a zoho_sync_mapping entry
        $builder = $db->table('invoices i')
            ->select('i.id, i.invoice_number, m.zoho_record_id')
            ->join('zoho_sync_mapping m', "m.local_record_id = i.id AND m.module = 'invoices'", 'inner')
            ->where('NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.invoice_id = i.id)', null, false)
            ->where('m.zoho_record_id IS NOT NULL', null, false)
            ->where('m.zoho_record_id !=', '')
            ->orderBy('i.id', 'ASC');

        if ($limit > 0) {
            $builder->limit($limit);
        }

        $targets = $builder->get()->getResultArray();
        $totalFound = count($targets);

        CLI::write("Found {$totalFound} invoice(s) missing line items.", 'yellow');
        if ($totalFound === 0) {
            CLI::write("All mapped invoices already have line items!", 'green');
            return EXIT_SUCCESS;
        }

        $processed = 0;
        $succeeded = 0;
        $failed    = 0;

        foreach ($targets as $target) {
            $localId = (int) $target['id'];
            $invNum  = $target['invoice_number'];
            $processed++;

            CLI::print("[$processed/$totalFound] Invoice #{$invNum} (ID: {$localId})... ");

            try {
                $res = $invSync->pullSingleInvoiceFromZoho($localId);
                if (!empty($res['success'])) {
                    $succeeded++;
                    CLI::write("OK", 'green');
                } else {
                    $failed++;
                    CLI::write("FAILED: " . ($res['message'] ?? 'Error'), 'red');
                }
            } catch (\Throwable $e) {
                $failed++;
                CLI::write("EXCEPTION: " . $e->getMessage(), 'red');
            }

            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        CLI::write("\n=== Backfill Summary ===", 'cyan');
        CLI::write("Total Processed: {$processed} | Succeeded: {$succeeded} | Failed: {$failed}", $failed > 0 ? 'yellow' : 'green');

        return EXIT_SUCCESS;
    }
}
