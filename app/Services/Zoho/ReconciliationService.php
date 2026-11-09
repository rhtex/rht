<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncHistoryModel;
use App\Models\Zoho\ZohoSyncMappingModel;

class ReconciliationService
{
    protected ZohoApiClient $client;
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncMappingModel $mappingModel;
    protected ZohoSyncHistoryModel $historyModel;

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client       = $client ?? new ZohoApiClient();
        $this->settingModel = new ZohoSettingModel();
        $this->mappingModel = new ZohoSyncMappingModel();
        $this->historyModel = new ZohoSyncHistoryModel();
    }

    /**
     * Generate a comprehensive Reconciliation Report comparing CI4 ERP against Zoho Books.
     */
    public function runReconciliation(bool $fetchLiveZoho = true, ?int $userId = null): array
    {
        $db     = \Config\Database::connect();
        $config = $this->settingModel->getConfig(false);
        $canQueryZoho = $fetchLiveZoho && !empty($config['is_enabled']) && !empty($config['organization_id']) && !empty($config['has_refresh_token']);

        $run = null;
        if ($fetchLiveZoho) {
            $run = $this->historyModel->startRun('RECONCILIATION', 'all', $userId);
        }

        // 1. Local ERP Financial & Record Summaries
        $localInvRow = $db->table('invoices')
            ->select('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(balance),0) as balance')
            ->where('status !=', 'Void')
            ->get()->getRowArray();

        $localBillRow = $db->table('bills')
            ->select('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(balance),0) as balance')
            ->where('status !=', 'Void')
            ->get()->getRowArray();

        $localQuotRow = $db->table('quotations')
            ->select('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total')
            ->get()->getRowArray();

        $localSoRow = $db->table('sales_orders')
            ->select('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total')
            ->where('status !=', 'Void')
            ->get()->getRowArray();

        $localPoRow = $db->tableExists('purchase_orders')
            ? $db->table('purchase_orders')->select('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total')->where('status !=', 'Void')->get()->getRowArray()
            : ['cnt' => 0, 'total' => 0];

        $localCnRow = $db->table('sales_returns')
            ->select('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total')
            ->get()->getRowArray();

        $localVcRow = $db->table('vendor_credits')
            ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as total')
            ->get()->getRowArray();

        $localCustPayRow = $db->table('invoice_payments')
            ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as total')
            ->get()->getRowArray();

        $localVendPayRow = $db->table('payments')
            ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as total')
            ->get()->getRowArray();

        $localExpRow = $db->table('expenses')
            ->select('COUNT(*) as cnt, COALESCE(SUM(amount),0) as total')
            ->get()->getRowArray();

        $localModules = [
            'customers'         => ['label' => 'Customers',             'local_count' => (int) $db->table('customers')->countAllResults(), 'local_total' => (float) ($localInvRow['balance'] ?? 0), 'amount_label' => 'Receivable Balance'],
            'vendors'           => ['label' => 'Suppliers / Vendors',   'local_count' => (int) $db->table('vendors')->countAllResults(),   'local_total' => (float) ($localBillRow['balance'] ?? 0), 'amount_label' => 'Payable Balance'],
            'items'             => ['label' => 'Items / Products',      'local_count' => (int) $db->table('products')->countAllResults(),  'local_total' => 0.0, 'amount_label' => 'N/A'],
            'quotations'        => ['label' => 'Quotations / Estimates','local_count' => (int) ($localQuotRow['cnt'] ?? 0),                'local_total' => (float) ($localQuotRow['total'] ?? 0), 'amount_label' => 'Total Quoted'],
            'sales_orders'      => ['label' => 'Sales Orders',          'local_count' => (int) ($localSoRow['cnt'] ?? 0),                  'local_total' => (float) ($localSoRow['total'] ?? 0), 'amount_label' => 'Total Ordered'],
            'invoices'          => ['label' => 'Sales Invoices',        'local_count' => (int) ($localInvRow['cnt'] ?? 0),                 'local_total' => (float) ($localInvRow['total'] ?? 0), 'amount_label' => 'Total Invoiced'],
            'credit_notes'      => ['label' => 'Credit Notes',          'local_count' => (int) ($localCnRow['cnt'] ?? 0),                  'local_total' => (float) ($localCnRow['total'] ?? 0), 'amount_label' => 'Total Credited'],
            'customer_payments' => ['label' => 'Customer Receipts',     'local_count' => (int) ($localCustPayRow['cnt'] ?? 0),             'local_total' => (float) ($localCustPayRow['total'] ?? 0), 'amount_label' => 'Total Received'],
            'purchase_orders'   => ['label' => 'Purchase Orders',       'local_count' => (int) ($localPoRow['cnt'] ?? 0),                  'local_total' => (float) ($localPoRow['total'] ?? 0), 'amount_label' => 'Total PO Value'],
            'bills'             => ['label' => 'Purchase Bills',        'local_count' => (int) ($localBillRow['cnt'] ?? 0),                'local_total' => (float) ($localBillRow['total'] ?? 0), 'amount_label' => 'Total Billed'],
            'vendor_credits'    => ['label' => 'Vendor Credits',        'local_count' => (int) ($localVcRow['cnt'] ?? 0),                  'local_total' => (float) ($localVcRow['total'] ?? 0), 'amount_label' => 'Total Vendor Credits'],
            'vendor_payments'   => ['label' => 'Vendor Payments',       'local_count' => (int) ($localVendPayRow['cnt'] ?? 0),             'local_total' => (float) ($localVendPayRow['total'] ?? 0), 'amount_label' => 'Total Paid'],
            'expenses'          => ['label' => 'Expenses',              'local_count' => (int) ($localExpRow['cnt'] ?? 0),                 'local_total' => (float) ($localExpRow['total'] ?? 0), 'amount_label' => 'Total Expenses'],
        ];

        $zohoLive = [];
        if ($canQueryZoho) {
            $zohoLive = $this->fetchLiveZohoSummary();
        }

        $reportRows = [];
        $discrepancyCount = 0;

        foreach ($localModules as $modKey => $info) {
            $mappedCount = (int) $db->table('zoho_sync_mapping')
                ->where('module', $modKey)
                ->where('sync_status', 'SYNCED')
                ->countAllResults();

            $failedCount = (int) $db->table('zoho_sync_mapping')
                ->where('module', $modKey)
                ->where('sync_status', 'FAILED')
                ->countAllResults();

            $conflictCount = (int) $db->table('zoho_sync_conflicts')
                ->where('module', $modKey)
                ->where('status', 'OPEN')
                ->countAllResults();

            $zCount = $zohoLive[$modKey]['count'] ?? $mappedCount;
            $zTotal = $zohoLive[$modKey]['total'] ?? $info['local_total'];

            $unmappedCount = max(0, $info['local_count'] - $mappedCount);
            $amountDiff    = round($info['local_total'] - $zTotal, 2);
            $isMatched     = ($unmappedCount === 0 && $failedCount === 0 && $conflictCount === 0 && abs($amountDiff) < 1.00);

            if (!$isMatched) {
                $discrepancyCount++;
            }

            $reportRows[$modKey] = [
                'module'         => $modKey,
                'label'          => $info['label'],
                'amount_label'   => $info['amount_label'],
                'local_count'    => $info['local_count'],
                'mapped_count'   => $mappedCount,
                'zoho_count'     => $zCount,
                'unmapped_count' => $unmappedCount,
                'failed_count'   => $failedCount,
                'conflict_count' => $conflictCount,
                'local_total'    => $info['local_total'],
                'zoho_total'     => $zTotal,
                'amount_diff'    => $amountDiff,
                'is_matched'     => $isMatched,
                'live_queried'   => $canQueryZoho && isset($zohoLive[$modKey]),
            ];
        }

        if ($run) {
            $this->historyModel->completeRun($run['id'], [
                'total_processed' => count($reportRows),
                'conflict_count'  => $discrepancyCount,
                'summary'         => $reportRows,
            ]);
        }

        return [
            'success'           => true,
            'generated_at'      => date('Y-m-d H:i:s'),
            'live_zoho_queried' => $canQueryZoho,
            'discrepancy_count' => $discrepancyCount,
            'rows'              => $reportRows,
        ];
    }

    protected function fetchLiveZohoSummary(): array
    {
        $summary = [];

        // Customers
        $custRes = $this->client->getAllPages('/contacts', 'contacts', ['contact_type' => 'customer'], 10, ['module' => 'reconciliation', 'operation' => 'RECON_CUSTOMERS']);
        if (!empty($custRes['success'])) {
            $recv = 0.0;
            foreach ($custRes['records'] as $c) {
                $recv += (float) ($c['outstanding_receivable_amount'] ?? 0);
            }
            $summary['customers'] = ['count' => count($custRes['records']), 'total' => round($recv, 2)];
        }

        // Vendors
        $vendRes = $this->client->getAllPages('/contacts', 'contacts', ['contact_type' => 'vendor'], 10, ['module' => 'reconciliation', 'operation' => 'RECON_VENDORS']);
        if (!empty($vendRes['success'])) {
            $pay = 0.0;
            foreach ($vendRes['records'] as $v) {
                $pay += (float) ($v['outstanding_payable_amount'] ?? 0);
            }
            $summary['vendors'] = ['count' => count($vendRes['records']), 'total' => round($pay, 2)];
        }

        // Items
        $itemRes = $this->client->getAllPages('/items', 'items', [], 10, ['module' => 'reconciliation', 'operation' => 'RECON_ITEMS']);
        if (!empty($itemRes['success'])) {
            $summary['items'] = ['count' => count($itemRes['records']), 'total' => 0.0];
        }

        // Invoices
        $invRes = $this->client->getAllPages('/invoices', 'invoices', [], 10, ['module' => 'reconciliation', 'operation' => 'RECON_INVOICES']);
        if (!empty($invRes['success'])) {
            $cnt = 0;
            $tot = 0.0;
            foreach ($invRes['records'] as $i) {
                if (strtolower((string) ($i['status'] ?? '')) === 'void') {
                    continue;
                }
                $cnt++;
                $tot += (float) ($i['total'] ?? 0);
            }
            $summary['invoices'] = ['count' => $cnt, 'total' => round($tot, 2)];
        }

        // Bills
        $billRes = $this->client->getAllPages('/bills', 'bills', [], 10, ['module' => 'reconciliation', 'operation' => 'RECON_BILLS']);
        if (!empty($billRes['success'])) {
            $cnt = 0;
            $tot = 0.0;
            foreach ($billRes['records'] as $b) {
                if (strtolower((string) ($b['status'] ?? '')) === 'void') {
                    continue;
                }
                $cnt++;
                $tot += (float) ($b['total'] ?? 0);
            }
            $summary['bills'] = ['count' => $cnt, 'total' => round($tot, 2)];
        }

        return $summary;
    }
}

