<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncConflictModel;
use App\Models\Zoho\ZohoSyncHistoryModel;
use App\Models\Zoho\ZohoSyncMappingModel;
use App\Models\Zoho\ZohoSyncQueueModel;
use App\Services\Zoho\Modules\AbstractModuleSyncService;
use App\Services\Zoho\Modules\BillSyncService;
use App\Services\Zoho\Modules\CreditNoteSyncService;
use App\Services\Zoho\Modules\CustomerPaymentSyncService;
use App\Services\Zoho\Modules\CustomerSyncService;
use App\Services\Zoho\Modules\EstimateSyncService;
use App\Services\Zoho\Modules\ExpenseSyncService;
use App\Services\Zoho\Modules\InvoiceSyncService;
use App\Services\Zoho\Modules\ItemSyncService;
use App\Services\Zoho\Modules\PurchaseOrderSyncService;
use App\Services\Zoho\Modules\SalesOrderSyncService;
use App\Services\Zoho\Modules\VendorCreditSyncService;
use App\Services\Zoho\Modules\VendorPaymentSyncService;
use App\Services\Zoho\Modules\VendorSyncService;

class ZohoSyncOrchestrator
{
    protected ZohoApiClient $client;
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncMappingModel $mappingModel;
    protected ZohoSyncQueueModel $queueModel;
    protected ZohoSyncHistoryModel $historyModel;
    protected ZohoSyncConflictModel $conflictModel;

    public const ORDERED_MODULES = [
        'customers'         => ['label' => 'Customers',              'table' => 'customers',         'icon' => 'fa-user-friends'],
        'vendors'           => ['label' => 'Suppliers / Vendors',    'table' => 'vendors',           'icon' => 'fa-store'],
        'items'             => ['label' => 'Items / Products',       'table' => 'products',          'icon' => 'fa-boxes'],
        'quotations'        => ['label' => 'Quotations / Estimates', 'table' => 'quotations',        'icon' => 'fa-file-alt'],
        'sales_orders'      => ['label' => 'Sales Orders',           'table' => 'sales_orders',      'icon' => 'fa-shopping-cart'],
        'invoices'          => ['label' => 'Sales Invoices',         'table' => 'invoices',          'icon' => 'fa-file-invoice'],
        'credit_notes'      => ['label' => 'Credit Notes / Returns', 'table' => 'sales_returns',     'icon' => 'fa-undo'],
        'customer_payments' => ['label' => 'Customer Receipts',      'table' => 'invoice_payments',  'icon' => 'fa-receipt'],
        'purchase_orders'   => ['label' => 'Purchase Orders',        'table' => 'purchase_orders',   'icon' => 'fa-clipboard-list'],
        'bills'             => ['label' => 'Purchase Bills',         'table' => 'bills',             'icon' => 'fa-file-invoice-dollar'],
        'vendor_credits'    => ['label' => 'Vendor Credits',         'table' => 'vendor_credits',    'icon' => 'fa-undo-alt'],
        'vendor_payments'   => ['label' => 'Vendor Payments',        'table' => 'payments',          'icon' => 'fa-money-check-alt'],
        'expenses'          => ['label' => 'Expenses',               'table' => 'expenses',          'icon' => 'fa-wallet'],
    ];

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client        = $client ?? new ZohoApiClient();
        $this->settingModel  = new ZohoSettingModel();
        $this->mappingModel  = new ZohoSyncMappingModel();
        $this->queueModel    = new ZohoSyncQueueModel();
        $this->historyModel  = new ZohoSyncHistoryModel();
        $this->conflictModel = new ZohoSyncConflictModel();
    }

    public function getModuleService(string $module): AbstractModuleSyncService
    {
        return match ($module) {
            'customers'         => new CustomerSyncService($this->client),
            'vendors'           => new VendorSyncService($this->client),
            'items'             => new ItemSyncService($this->client),
            'quotations'        => new EstimateSyncService($this->client),
            'sales_orders'      => new SalesOrderSyncService($this->client),
            'invoices'          => new InvoiceSyncService($this->client),
            'credit_notes'      => new CreditNoteSyncService($this->client),
            'customer_payments' => new CustomerPaymentSyncService($this->client),
            'purchase_orders'   => new PurchaseOrderSyncService($this->client),
            'bills'             => new BillSyncService($this->client),
            'vendor_credits'    => new VendorCreditSyncService($this->client),
            'vendor_payments'   => new VendorPaymentSyncService($this->client),
            'expenses'          => new ExpenseSyncService($this->client),
            default             => throw new \InvalidArgumentException("Unsupported Zoho sync module: {$module}"),
        };
    }

    /**
     * Automatic CI4 -> Zoho hook entrypoint called after local Create / Update / Delete / Void.
     * Never throws uncaught exceptions so local ERP transactions always succeed even if Zoho is offline.
     */
    public function handleLocalChange(string $module, int $localId, string $operation = 'UPDATE'): array
    {
        // Guard against infinite synchronization loops
        if (ZohoSyncContext::isZohoOrSystem()) {
            return ['success' => true, 'skipped' => true, 'reason' => 'Change originated from Zoho/System'];
        }

        try {
            $config = $this->settingModel->getConfig(false);

            // Always enqueue in zoho_sync_queue if integration is enabled
            if (empty($config['is_enabled'])) {
                return ['success' => true, 'skipped' => true, 'reason' => 'Zoho integration is disabled'];
            }

            $queueId = $this->queueModel->enqueue([
                'module'          => $module,
                'local_record_id' => $localId,
                'operation'       => $operation,
                'direction'       => 'TO_ZOHO',
                'source'          => ZohoSyncContext::getSource(),
            ]);

            if (empty($config['auto_sync_enabled']) || ($config['sync_mode'] ?? 'immediate') === 'queue_cron') {
                return [
                    'success'  => true,
                    'queued'   => true,
                    'queue_id' => $queueId,
                    'message'  => 'Queued for Zoho synchronization.',
                ];
            }

            // Process immediately
            return $this->processQueueItem($queueId);
        } catch (\Throwable $e) {
            log_message('error', "ZohoSyncOrchestrator::handleLocalChange({$module}, #{$localId}, {$operation}) failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a single queue item by ID.
     */
    public function processQueueItem(int $queueId): array
    {
        $item = $this->queueModel->find($queueId);
        if (!$item) {
            return ['success' => false, 'message' => 'Queue item not found'];
        }

        $attempts = (int) ($item['attempts'] ?? 0) + 1;
        $this->queueModel->update($queueId, [
            'status'    => 'PROCESSING',
            'attempts'  => $attempts,
            'locked_at' => date('Y-m-d H:i:s'),
        ]);

        try {
            $service = $this->getModuleService($item['module']);
            $res     = $service->pushToZoho((int) $item['local_record_id'], $item['operation']);

            if (!empty($res['success'])) {
                $this->queueModel->update($queueId, [
                    'status'         => 'COMPLETED',
                    'zoho_record_id' => $res['zoho_record_id'] ?? $item['zoho_record_id'],
                    'error_message'  => null,
                    'locked_at'      => null,
                ]);
                $this->settingModel->saveConfig(['last_sync_at' => date('Y-m-d H:i:s')]);
                return $res;
            }

            // If rate-limited (100 req/min or 1,000 calls/day quota), keep as PENDING and schedule retry without burning attempts
            if (!empty($res['rate_limited']) || ($res['code'] ?? 0) === 429 || ($res['http_code'] ?? 0) === 429) {
                $delaySec = (($res['reason'] ?? '') === 'DAILY_QUOTA_REACHED') ? 3600 : 65;
                $this->queueModel->update($queueId, [
                    'status'        => 'PENDING',
                    'attempts'      => max(0, $attempts - 1),
                    'next_retry_at' => date('Y-m-d H:i:s', time() + $delaySec),
                    'error_message' => $res['message'] ?? 'Deferred due to Zoho API rate/daily limit.',
                    'locked_at'     => null,
                ]);
                return $res;
            }

            // Schedule exponential backoff retry (2^attempts minutes)
            $backoffMinutes = min(60, (int) pow(2, $attempts));
            $this->queueModel->update($queueId, [
                'status'        => 'FAILED',
                'next_retry_at' => date('Y-m-d H:i:s', time() + ($backoffMinutes * 60)),
                'error_message' => $res['message'] ?? 'Unknown Zoho API error',
                'locked_at'     => null,
            ]);

            return $res;
        } catch (\Throwable $e) {
            $backoffMinutes = min(60, (int) pow(2, $attempts));
            $this->queueModel->update($queueId, [
                'status'        => 'FAILED',
                'next_retry_at' => date('Y-m-d H:i:s', time() + ($backoffMinutes * 60)),
                'error_message' => $e->getMessage(),
                'locked_at'     => null,
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process pending items in the queue (FIFO, rate-limited, respecting next_retry_at).
     */
    public function processPendingQueue(int $limit = 50): array
    {
        $now = date('Y-m-d H:i:s');
        $items = $this->queueModel
            ->groupStart()
                ->where('status', 'PENDING')
                ->orGroupStart()
                    ->where('status', 'FAILED')
                    ->where('attempts <', 5)
                    ->where('next_retry_at <=', $now)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->findAll($limit);

        $processed = 0;
        $failed    = 0;
        foreach ($items as $item) {
            $res = $this->processQueueItem((int) $item['id']);
            $processed++;
            if (empty($res['success'])) {
                $failed++;
            }
        }

        return [
            'success'       => true,
            'processed'     => $processed,
            'failed_count'  => $failed,
            'status'        => 'completed',
        ];
    }

    /**
     * Manual / Scheduled: Sync from Zoho Books -> CI4 ERP.
     */
    public function syncFromZoho(?string $moduleFilter = null, bool $incremental = false, ?int $userId = null): array
    {
        $run = $this->historyModel->startRun(
            $moduleFilter ? 'MODULE_SYNC' : 'MANUAL_FROM_ZOHO',
            $moduleFilter,
            $userId
        );
        ZohoSyncContext::setBatchId($run['batch_id']);

        $modules = $this->resolveModuleList($moduleFilter);
        $totals  = [
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'deleted_count'   => 0,
            'skipped_count'   => 0,
            'conflict_count'  => 0,
            'failed_count'    => 0,
            'summary'         => [],
        ];

        $config = $this->settingModel->getConfig(false);
        if (empty($config['has_refresh_token'])) {
            $msg = 'Zoho Books is not connected. Please go to Settings -> Integrations -> Zoho Books and click "Connect to Zoho Books (OAuth 2.0)".';
            return array_merge(['success' => false, 'message' => $msg, 'failed_count' => count($modules)], $totals);
        }
        if (empty($config['organization_id'])) {
            $msg = 'Zoho Organization ID is not selected. Please connect and select an organization under Settings -> Integrations -> Zoho Books.';
            return array_merge(['success' => false, 'message' => $msg, 'failed_count' => count($modules)], $totals);
        }

        try {
            // Prime Taxes & Chart of Accounts metadata cache first
            (new ZohoTaxMapperService($this->client))->ensureMetadataCache();

            foreach ($modules as $modKey) {
                $service = $this->getModuleService($modKey);
                $modRes  = $service->pullFromZoho($incremental, true);

                $totals['summary'][$modKey] = $modRes;
                if (empty($modRes['success'])) {
                    $totals['failed_count']++;
                    if (!empty($modRes['rate_limited']) || !empty($modRes['error']['rate_limited'])) {
                        $totals['rate_limited'] = true;
                        $totals['message']      = $modRes['message'] ?? 'Paused: Zoho API rate/daily limit reached.';
                        break;
                    }
                    continue;
                }

                $totals['total_processed'] += (int) ($modRes['total_processed'] ?? 0);
                $totals['created_count']   += (int) ($modRes['created_count'] ?? 0);
                $totals['updated_count']   += (int) ($modRes['updated_count'] ?? 0);
                $totals['skipped_count']   += (int) ($modRes['skipped_count'] ?? 0);
                $totals['conflict_count']  += (int) ($modRes['conflict_count'] ?? 0);
                $totals['failed_count']    += (int) ($modRes['failed_count'] ?? 0);
            }

            $this->settingModel->saveConfig(['last_sync_at' => date('Y-m-d H:i:s')]);
            $this->historyModel->completeRun($run['id'], $totals);
            ZohoSyncContext::setBatchId(null);

            return array_merge(['success' => true, 'batch_id' => $run['batch_id']], $totals);
        } catch (\Throwable $e) {
            $totals['failed_count']++;
            $totals['status'] = 'FAILED';
            $this->historyModel->completeRun($run['id'], $totals);
            ZohoSyncContext::setBatchId(null);
            return ['success' => false, 'message' => $e->getMessage(), 'batch_id' => $run['batch_id']];
        }
    }

    /**
     * Manual / Scheduled: Sync from CI4 ERP -> Zoho Books.
     */
    public function syncToZoho(?string $moduleFilter = null, ?int $userId = null): array
    {
        $run = $this->historyModel->startRun(
            $moduleFilter ? 'MODULE_SYNC' : 'MANUAL_TO_ZOHO',
            $moduleFilter,
            $userId
        );
        ZohoSyncContext::setBatchId($run['batch_id']);

        $modules = $this->resolveModuleList($moduleFilter);
        $db      = \Config\Database::connect();

        $totals = [
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'deleted_count'   => 0,
            'skipped_count'   => 0,
            'conflict_count'  => 0,
            'failed_count'    => 0,
            'summary'         => [],
        ];

        try {
            (new ZohoTaxMapperService($this->client))->ensureMetadataCache();

            foreach ($modules as $modKey) {
                $modMeta = self::ORDERED_MODULES[$modKey] ?? null;
                if (!$modMeta || !$db->tableExists($modMeta['table'])) {
                    continue;
                }

                $service = $this->getModuleService($modKey);
                $rows    = $db->table($modMeta['table'])->select('id')->orderBy('id', 'ASC')->get()->getResultArray();
                $modStats = ['processed' => 0, 'synced' => 0, 'skipped' => 0, 'failed' => 0];

                foreach ($rows as $row) {
                    $localId = (int) $row['id'];
                    $totals['total_processed']++;
                    $modStats['processed']++;

                    $existingMap = $this->mappingModel->findByLocal($modKey, $localId);
                    $res         = $service->pushToZoho($localId, $existingMap ? 'UPDATE' : 'CREATE');

                    if (!empty($res['skipped'])) {
                        $totals['skipped_count']++;
                        $modStats['skipped']++;
                    } elseif (!empty($res['success'])) {
                        if ($existingMap) {
                            $totals['updated_count']++;
                        } else {
                            $totals['created_count']++;
                        }
                        $modStats['synced']++;
                    } else {
                        $totals['failed_count']++;
                        $modStats['failed']++;
                        if (!empty($res['rate_limited']) || ($res['code'] ?? 0) === 429 || ($res['http_code'] ?? 0) === 429) {
                            // Enqueue remaining or stop batch when daily/minute quota is reached
                            $this->queueModel->enqueue([
                                'module'          => $modKey,
                                'local_record_id' => $localId,
                                'operation'       => $existingMap ? 'UPDATE' : 'CREATE',
                                'direction'       => 'TO_ZOHO',
                                'source'          => 'LOCAL',
                            ]);
                            $totals['rate_limited'] = true;
                            $totals['message']      = $res['message'] ?? 'Paused: Zoho API rate/daily limit reached.';
                            break 2;
                        }
                    }
                }

                $totals['summary'][$modKey] = $modStats;
            }

            $this->settingModel->saveConfig(['last_sync_at' => date('Y-m-d H:i:s')]);
            $this->historyModel->completeRun($run['id'], $totals);
            ZohoSyncContext::setBatchId(null);

            return array_merge(['success' => true, 'batch_id' => $run['batch_id']], $totals);
        } catch (\Throwable $e) {
            $totals['failed_count']++;
            $totals['status'] = 'FAILED';
            $this->historyModel->completeRun($run['id'], $totals);
            ZohoSyncContext::setBatchId(null);
            return ['success' => false, 'message' => $e->getMessage(), 'batch_id' => $run['batch_id']];
        }
    }

    /**
     * Full Bidirectional Sync:
     * 1. Pulls changes & detects conflicts from Zoho Books -> CI4 ERP.
     * 2. Pushes unmapped and modified local records from CI4 ERP -> Zoho Books (skipping any records with OPEN conflicts).
     */
    public function fullBidirectionalSync(?string $moduleFilter = null, ?int $userId = null): array
    {
        $run = $this->historyModel->startRun('FULL_BIDIRECTIONAL', $moduleFilter, $userId);
        ZohoSyncContext::setBatchId($run['batch_id']);

        $fromRes = $this->syncFromZoho($moduleFilter, false, $userId);
        $toRes   = $this->syncToZoho($moduleFilter, $userId);

        $combined = [
            'total_processed' => (int) ($fromRes['total_processed'] ?? 0) + (int) ($toRes['total_processed'] ?? 0),
            'created_count'   => (int) ($fromRes['created_count'] ?? 0) + (int) ($toRes['created_count'] ?? 0),
            'updated_count'   => (int) ($fromRes['updated_count'] ?? 0) + (int) ($toRes['updated_count'] ?? 0),
            'deleted_count'   => (int) ($fromRes['deleted_count'] ?? 0) + (int) ($toRes['deleted_count'] ?? 0),
            'skipped_count'   => (int) ($fromRes['skipped_count'] ?? 0) + (int) ($toRes['skipped_count'] ?? 0),
            'conflict_count'  => (int) ($fromRes['conflict_count'] ?? 0) + (int) ($toRes['conflict_count'] ?? 0),
            'failed_count'    => (int) ($fromRes['failed_count'] ?? 0) + (int) ($toRes['failed_count'] ?? 0),
            'summary'         => [
                'from_zoho' => $fromRes['summary'] ?? [],
                'to_zoho'   => $toRes['summary'] ?? [],
            ],
        ];

        $this->historyModel->completeRun($run['id'], $combined);
        ZohoSyncContext::setBatchId(null);

        return array_merge(['success' => true, 'batch_id' => $run['batch_id']], $combined);
    }

    /**
     * Sync specific selected local records for a given module.
     */
    public function syncSelectedRecords(string $module, array $localIds, ?int $userId = null): array
    {
        $run = $this->historyModel->startRun('RECORD_SYNC', $module, $userId);
        ZohoSyncContext::setBatchId($run['batch_id']);

        $service = $this->getModuleService($module);
        $stats   = [
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'skipped_count'   => 0,
            'failed_count'    => 0,
            'results'         => [],
        ];

        foreach ($localIds as $rawId) {
            $localId = (int) $rawId;
            if ($localId <= 0) {
                continue;
            }
            $stats['total_processed']++;
            $existing = $this->mappingModel->findByLocal($module, $localId);
            $res      = $service->pushToZoho($localId, $existing ? 'UPDATE' : 'CREATE');
            $stats['results'][$localId] = $res;

            if (!empty($res['skipped'])) {
                $stats['skipped_count']++;
            } elseif (!empty($res['success'])) {
                if ($existing) {
                    $stats['updated_count']++;
                } else {
                    $stats['created_count']++;
                }
            } else {
                $stats['failed_count']++;
            }
        }

        $this->settingModel->saveConfig(['last_sync_at' => date('Y-m-d H:i:s')]);
        $this->historyModel->completeRun($run['id'], $stats);
        ZohoSyncContext::setBatchId(null);

        return array_merge(['success' => $stats['failed_count'] === 0, 'batch_id' => $run['batch_id']], $stats);
    }

    /**
     * Retry all FAILED / PENDING items in zoho_sync_queue and any FAILED mappings.
     */
    public function retryFailed(?string $moduleFilter = null, int $limit = 100, ?int $userId = null): array
    {
        $run = $this->historyModel->startRun('RETRY_FAILED', $moduleFilter, $userId);
        ZohoSyncContext::setBatchId($run['batch_id']);

        // Reset next_retry_at on failed queue items so they run immediately
        $qBuilder = $this->queueModel->whereIn('status', ['FAILED', 'PENDING']);
        if (!empty($moduleFilter)) {
            $qBuilder->where('module', $moduleFilter);
        }
        $items = $qBuilder->orderBy('priority', 'ASC')->findAll($limit);

        // Also check zoho_sync_mapping where sync_status = 'FAILED'
        $mBuilder = $this->mappingModel->where('sync_status', 'FAILED');
        if (!empty($moduleFilter)) {
            $mBuilder->where('module', $moduleFilter);
        }
        $failedMappings = $mBuilder->findAll($limit);
        foreach ($failedMappings as $fm) {
            $this->queueModel->enqueue([
                'module'          => $fm['module'],
                'local_record_id' => (int) $fm['local_record_id'],
                'zoho_record_id'  => $fm['zoho_record_id'],
                'operation'       => 'UPDATE',
            ]);
        }

        $items = $this->queueModel->fetchRunnable($limit, $moduleFilter);

        $stats = [
            'total_processed' => 0,
            'updated_count'   => 0,
            'failed_count'    => 0,
        ];

        foreach ($items as $item) {
            $stats['total_processed']++;
            $res = $this->processQueueItem((int) $item['id']);
            if (!empty($res['success'])) {
                $stats['updated_count']++;
            } else {
                $stats['failed_count']++;
            }
        }

        $this->historyModel->completeRun($run['id'], $stats);
        ZohoSyncContext::setBatchId(null);

        return array_merge(['success' => true, 'batch_id' => $run['batch_id']], $stats);
    }

    /**
     * Resolve an open conflict by applying either LOCAL version (pushes to Zoho) or ZOHO version (pulls to CI4).
     */
    public function resolveConflict(int $conflictId, string $choice, ?int $userId = null, ?string $notes = null): array
    {
        $conflict = $this->conflictModel->find($conflictId);
        if (!$conflict) {
            return ['success' => false, 'message' => 'Conflict record not found.'];
        }

        $module  = $conflict['module'];
        $localId = (int) $conflict['local_record_id'];
        $zohoId  = (string) $conflict['zoho_record_id'];
        $choice  = strtoupper(trim($choice));

        $service = $this->getModuleService($module);

        if ($choice === 'LOCAL') {
            // Clear local_version_hash temporarily so pushToZoho forces an update
            $mapping = $this->mappingModel->findByLocal($module, $localId);
            if ($mapping) {
                $this->mappingModel->update($mapping['id'], ['local_version_hash' => null, 'sync_status' => 'PENDING']);
            }
            $res = $service->pushToZoho($localId, 'UPDATE');
            if (empty($res['success'])) {
                return $res;
            }
            $this->conflictModel->update($conflictId, [
                'status'           => 'RESOLVED_LOCAL',
                'resolved_by'      => $userId,
                'resolved_at'      => date('Y-m-d H:i:s'),
                'resolution_notes' => $notes ?: 'Chose CI4 ERP Local version.',
            ]);
            return ['success' => true, 'message' => 'Conflict resolved using CI4 Local version and pushed to Zoho Books.'];
        }

        if ($choice === 'ZOHO') {
            $endpointMap = [
                'customers'         => ["/contacts/{$zohoId}", 'contact'],
                'vendors'           => ["/contacts/{$zohoId}", 'contact'],
                'items'             => ["/items/{$zohoId}", 'item'],
                'quotations'        => ["/estimates/{$zohoId}", 'estimate'],
                'sales_orders'      => ["/salesorders/{$zohoId}", 'salesorder'],
                'invoices'          => ["/invoices/{$zohoId}", 'invoice'],
                'credit_notes'      => ["/creditnotes/{$zohoId}", 'creditnote'],
                'customer_payments' => ["/customerpayments/{$zohoId}", 'payment'],
                'purchase_orders'   => ["/purchaseorders/{$zohoId}", 'purchaseorder'],
                'bills'             => ["/bills/{$zohoId}", 'bill'],
                'vendor_credits'    => ["/vendorcredits/{$zohoId}", 'vendor_credit'],
                'vendor_payments'   => ["/vendorpayments/{$zohoId}", 'vendorpayment'],
                'expenses'          => ["/expenses/{$zohoId}", 'expense'],
            ];

            $epInfo = $endpointMap[$module] ?? null;
            if (!$epInfo) {
                return ['success' => false, 'message' => 'Unsupported module for Zoho conflict pull.'];
            }

            $detailRes = $this->client->get($epInfo[0], [], [
                'module'         => $module,
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'RESOLVE_CONFLICT_PULL',
            ]);
            if (empty($detailRes['success']) || empty($detailRes['data'][$epInfo[1]])) {
                return ['success' => false, 'message' => $detailRes['message'] ?? 'Failed to fetch latest record from Zoho Books.'];
            }

            $applyRes = $service->applyZohoRecordToLocal($detailRes['data'][$epInfo[1]], true);
            if (empty($applyRes['success'])) {
                return $applyRes;
            }

            $this->conflictModel->update($conflictId, [
                'status'           => 'RESOLVED_ZOHO',
                'resolved_by'      => $userId,
                'resolved_at'      => date('Y-m-d H:i:s'),
                'resolution_notes' => $notes ?: 'Chose Zoho Books version.',
            ]);
            return ['success' => true, 'message' => 'Conflict resolved using Zoho Books version and applied locally.'];
        }

        return ['success' => false, 'message' => 'Invalid conflict resolution choice.'];
    }

    /**
     * Module-by-module statistics for the Synchronization Dashboard.
     */
    public function getModuleStatistics(): array
    {
        $db    = \Config\Database::connect();
        $stats = [];

        foreach (self::ORDERED_MODULES as $modKey => $meta) {
            $localCount = $db->tableExists($meta['table'])
                ? (int) $db->table($meta['table'])->countAllResults()
                : 0;

            $syncedCount = (int) $db->table('zoho_sync_mapping')
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

            $pendingQueue = (int) $db->table('zoho_sync_queue')
                ->where('module', $modKey)
                ->whereIn('status', ['PENDING', 'PROCESSING'])
                ->countAllResults();

            $lastSyncRow = $db->table('zoho_sync_mapping')
                ->selectMax('last_successful_sync_at')
                ->where('module', $modKey)
                ->get()
                ->getRowArray();

            $stats[$modKey] = [
                'module'          => $modKey,
                'label'           => $meta['label'],
                'table'           => $meta['table'],
                'icon'            => $meta['icon'],
                'local_count'     => $localCount,
                'synced_count'    => $syncedCount,
                'unsynced_count'  => max(0, $localCount - $syncedCount),
                'failed_count'    => $failedCount,
                'conflict_count'  => $conflictCount,
                'pending_queue'   => $pendingQueue,
                'last_sync_at'    => $lastSyncRow['last_successful_sync_at'] ?? null,
            ];
        }

        return $stats;
    }

    protected function resolveModuleList(?string $moduleFilter): array
    {
        if (!empty($moduleFilter) && isset(self::ORDERED_MODULES[$moduleFilter])) {
            return [$moduleFilter];
        }
        return array_keys(self::ORDERED_MODULES);
    }
}
