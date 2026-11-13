<?php

namespace App\Controllers;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncConflictModel;
use App\Models\Zoho\ZohoSyncHistoryModel;
use App\Models\Zoho\ZohoSyncLogModel;
use App\Models\Zoho\ZohoSyncMappingModel;
use App\Models\Zoho\ZohoSyncQueueModel;
use App\Services\Zoho\ReconciliationService;
use App\Services\Zoho\ZohoRateLimiterService;
use App\Services\Zoho\ZohoSyncOrchestrator;
use App\Services\Zoho\ZohoSyncProgressService;

class ZohoSyncController extends BaseController
{
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncOrchestrator $orchestrator;
    protected ZohoSyncMappingModel $mappingModel;
    protected ZohoSyncQueueModel $queueModel;
    protected ZohoSyncLogModel $logModel;
    protected ZohoSyncConflictModel $conflictModel;
    protected ZohoSyncHistoryModel $historyModel;

    public function __construct()
    {
        $this->settingModel  = new ZohoSettingModel();
        $this->orchestrator  = new ZohoSyncOrchestrator();
        $this->mappingModel  = new ZohoSyncMappingModel();
        $this->queueModel    = new ZohoSyncQueueModel();
        $this->logModel      = new ZohoSyncLogModel();
        $this->conflictModel = new ZohoSyncConflictModel();
        $this->historyModel  = new ZohoSyncHistoryModel();
    }

    /**
     * Synchronization Dashboard (Settings -> Integrations -> Zoho Books -> Synchronization)
     */
    public function dashboard()
    {
        $config      = $this->settingModel->getConfig(false);
        $moduleStats = $this->orchestrator->getModuleStatistics();

        $totalMapped    = 0;
        $totalUnsynced  = 0;
        $totalFailed    = 0;
        $totalConflicts = $this->conflictModel->countOpen();
        $pendingQueue   = (int) $this->queueModel->whereIn('status', ['PENDING', 'PROCESSING'])->countAllResults();

        foreach ($moduleStats as $ms) {
            $totalMapped   += $ms['synced_count'];
            $totalUnsynced += $ms['unsynced_count'];
            $totalFailed   += $ms['failed_count'];
        }

        $recentHistory = $this->historyModel->orderBy('id', 'DESC')->findAll(15);
        $recentLogs    = $this->logModel->orderBy('id', 'DESC')->findAll(20);
        $rateUsage     = (new ZohoRateLimiterService())->getUsageStats($config);

        return view('zoho/dashboard', [
            'title'          => 'Zoho Books Synchronization Dashboard',
            'config'         => $config,
            'moduleStats'    => $moduleStats,
            'totalMapped'    => $totalMapped,
            'totalUnsynced'  => $totalUnsynced,
            'totalFailed'    => $totalFailed,
            'totalConflicts' => $totalConflicts,
            'pendingQueue'   => $pendingQueue,
            'recentHistory'  => $recentHistory,
            'recentLogs'     => $recentLogs,
            'modulesList'    => ZohoSyncOrchestrator::ORDERED_MODULES,
            'rateUsage'      => $rateUsage,
        ]);
    }

    /**
     * Action A: Sync from Zoho Books -> CI4 ERP
     */
    public function syncFromZoho()
    {
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        $module      = $this->request->getPost('module') ?: $this->request->getGet('module');
        $incremental = (bool) $this->request->getPost('incremental');
        $userId      = (int) (session('user_id') ?? 0) ?: null;
        $syncKey     = (string) ($this->request->getPost('sync_key') ?: ($userId ? 'user_' . $userId : 'guest'));

        ZohoSyncProgressService::update($syncKey, 5, 'Starting sync from Zoho Books...', 'running');
        $progressCallback = function (int $pct, string $msg, array $extra = []) use ($syncKey) {
            ZohoSyncProgressService::update($syncKey, $pct, $msg, 'running', $extra);
        };

        try {
            $res = $this->orchestrator->syncFromZoho($module ?: null, $incremental, $userId, $progressCallback);
            $isSuccess = !empty($res['success']);
            $status    = $isSuccess ? 'completed' : 'failed';
            $finalMsg  = $isSuccess ? 'Sync from Zoho Books completed' : ($res['message'] ?? 'Sync failed');
            ZohoSyncProgressService::update($syncKey, 100, $finalMsg, $status, ['summary' => $res]);
            return $this->respondSyncResult($res, 'Sync from Zoho Books completed');
        } catch (\Throwable $e) {
            ZohoSyncProgressService::update($syncKey, 100, 'Error: ' . $e->getMessage(), 'failed');
            return $this->respondSyncResult(['success' => false, 'message' => $e->getMessage()], 'Sync from Zoho Books failed');
        }
    }

    /**
     * Action B: Sync from CI4 ERP -> Zoho Books
     */
    public function syncToZoho()
    {
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        $module  = $this->request->getPost('module') ?: $this->request->getGet('module');
        $userId  = (int) (session('user_id') ?? 0) ?: null;
        $syncKey = (string) ($this->request->getPost('sync_key') ?: ($userId ? 'user_' . $userId : 'guest'));

        ZohoSyncProgressService::update($syncKey, 5, 'Starting sync to Zoho Books...', 'running');
        $progressCallback = function (int $pct, string $msg, array $extra = []) use ($syncKey) {
            ZohoSyncProgressService::update($syncKey, $pct, $msg, 'running', $extra);
        };

        try {
            $res = $this->orchestrator->syncToZoho($module ?: null, $userId, $progressCallback);
            $isSuccess = !empty($res['success']);
            $status    = $isSuccess ? 'completed' : 'failed';
            $finalMsg  = $isSuccess ? 'Sync to Zoho Books completed' : ($res['message'] ?? 'Sync failed');
            ZohoSyncProgressService::update($syncKey, 100, $finalMsg, $status, ['summary' => $res]);
            return $this->respondSyncResult($res, 'Sync to Zoho Books completed');
        } catch (\Throwable $e) {
            ZohoSyncProgressService::update($syncKey, 100, 'Error: ' . $e->getMessage(), 'failed');
            return $this->respondSyncResult(['success' => false, 'message' => $e->getMessage()], 'Sync to Zoho Books failed');
        }
    }

    /**
     * Action C: Full Bidirectional Sync
     */
    public function fullSync()
    {
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        $module  = $this->request->getPost('module') ?: $this->request->getGet('module');
        $userId  = (int) (session('user_id') ?? 0) ?: null;
        $syncKey = (string) ($this->request->getPost('sync_key') ?: ($userId ? 'user_' . $userId : 'guest'));

        ZohoSyncProgressService::update($syncKey, 5, 'Starting full bidirectional sync...', 'running');
        $progressCallback = function (int $pct, string $msg, array $extra = []) use ($syncKey) {
            ZohoSyncProgressService::update($syncKey, $pct, $msg, 'running', $extra);
        };

        try {
            $res = $this->orchestrator->fullBidirectionalSync($module ?: null, $userId, $progressCallback);
            $isSuccess = !empty($res['success']);
            $status    = $isSuccess ? 'completed' : 'failed';
            $finalMsg  = $isSuccess ? 'Full Bidirectional Sync completed' : ($res['message'] ?? 'Sync failed');
            ZohoSyncProgressService::update($syncKey, 100, $finalMsg, $status, ['summary' => $res]);
            return $this->respondSyncResult($res, 'Full Bidirectional Sync completed');
        } catch (\Throwable $e) {
            ZohoSyncProgressService::update($syncKey, 100, 'Error: ' . $e->getMessage(), 'failed');
            return $this->respondSyncResult(['success' => false, 'message' => $e->getMessage()], 'Full Bidirectional Sync failed');
        }
    }

    /**
     * Action D: Sync Selected Module (supports direction = from_zoho | to_zoho | bidirectional)
     */
    public function syncModule()
    {
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        $module    = (string) ($this->request->getPost('module') ?: $this->request->getGet('module'));
        $direction = (string) ($this->request->getPost('direction') ?: 'bidirectional');
        $userId    = (int) (session('user_id') ?? 0) ?: null;
        $syncKey   = (string) ($this->request->getPost('sync_key') ?: ($userId ? 'user_' . $userId : 'guest'));

        if ($module === '') {
            return $this->respondSyncResult(['success' => false, 'message' => 'Please select a module to synchronize.'], 'Module sync failed');
        }

        ZohoSyncProgressService::update($syncKey, 5, "Preparing {$module} ({$direction}) synchronization...", 'running');

        $progressCallback = function (int $pct, string $msg, array $extra = []) use ($syncKey) {
            ZohoSyncProgressService::update($syncKey, $pct, $msg, 'running', $extra);
        };

        try {
            $res = match ($direction) {
                'from_zoho' => $this->orchestrator->syncFromZoho($module, false, $userId, $progressCallback),
                'to_zoho'   => $this->orchestrator->syncToZoho($module, $userId, $progressCallback),
                default     => $this->orchestrator->fullBidirectionalSync($module, $userId, $progressCallback),
            };

            $isSuccess = !empty($res['success']);
            $finalPct  = 100;
            $finalMsg  = $isSuccess ? 'Synchronization finished successfully!' : ($res['message'] ?? 'Sync encountered an error.');
            $status    = $isSuccess ? 'completed' : 'failed';

            ZohoSyncProgressService::update($syncKey, $finalPct, $finalMsg, $status, ['summary' => $res]);

            return $this->respondSyncResult($res, 'Module (' . $module . ') synchronization completed');
        } catch (\Throwable $e) {
            ZohoSyncProgressService::update($syncKey, 100, 'Sync Exception: ' . $e->getMessage(), 'failed');
            return $this->respondSyncResult(['success' => false, 'message' => $e->getMessage()], 'Module sync failed');
        }
    }

    /**
     * Poll real-time progress for an ongoing synchronization task.
     */
    public function syncProgress()
    {
        $userId  = (int) (session('user_id') ?? 0) ?: null;
        $syncKey = (string) ($this->request->getGet('sync_key') ?: ($userId ? 'user_' . $userId : 'guest'));
        $data    = ZohoSyncProgressService::get($syncKey);

        return $this->response->setJSON($data);
    }

    /**
     * Action E: Sync Selected Records
     */
    public function syncSelectedRecords()
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        $module    = (string) $this->request->getPost('module');
        $recordIds = $this->request->getPost('record_ids');
        $userId    = (int) (session('user_id') ?? 0) ?: null;

        if (is_string($recordIds)) {
            $recordIds = array_filter(array_map('intval', explode(',', $recordIds)));
        } elseif (is_array($recordIds)) {
            $recordIds = array_filter(array_map('intval', $recordIds));
        } else {
            $recordIds = [];
        }

        if ($module === '' || empty($recordIds)) {
            return $this->respondSyncResult(['success' => false, 'message' => 'Please select a module and at least one record.'], 'Selected records sync');
        }

        $res = $this->orchestrator->syncSelectedRecords($module, $recordIds, $userId);
        return $this->respondSyncResult($res, 'Synchronized ' . count($recordIds) . ' selected record(s)');
    }

    /**
     * Quick single-record sync button handler from any ERP page.
     */
    public function syncSingleRecord(string $module, int $localId)
    {
        $userId = (int) (session('user_id') ?? 0) ?: null;
        $res    = $this->orchestrator->syncSelectedRecords($module, [$localId], $userId);
        $single = $res['results'][$localId] ?? $res;

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($single);
        }

        if (!empty($single['success'])) {
            return redirect()->back()->with('success', $single['message'] ?? 'Record synchronized with Zoho Books.');
        }
        return redirect()->back()->with('error', $single['message'] ?? 'Failed to synchronize record with Zoho Books.');
    }

    /**
     * Action F: Retry Failed Sync
     */
    public function retryFailed()
    {
        @set_time_limit(300);
        $module = $this->request->getPost('module') ?: $this->request->getGet('module');
        $userId = (int) (session('user_id') ?? 0) ?: null;

        $res = $this->orchestrator->retryFailed($module ?: null, 100, $userId);
        return $this->respondSyncResult($res, 'Retried failed synchronizations');
    }

    /**
     * AJAX helper to list records for the "Sync Selected Records" modal picker.
     */
    public function fetchModuleRecords(string $module)
    {
        $db   = \Config\Database::connect();
        $meta = ZohoSyncOrchestrator::ORDERED_MODULES[$module] ?? null;
        if (!$meta || !$db->tableExists($meta['table'])) {
            return $this->response->setJSON(['success' => false, 'records' => []]);
        }

        $labelCol = match ($module) {
            'customers', 'vendors' => 'name',
            'items'                => 'product_name',
            'quotations'           => 'quotation_number',
            'sales_orders'         => 'sales_order_number',
            'invoices'             => 'invoice_number',
            'credit_notes'         => 'return_number',
            'customer_payments', 'vendor_payments' => 'payment_number',
            'purchase_orders'      => 'purchase_order_number',
            'bills'                => 'bill_number',
            'vendor_credits'       => 'reference_no',
            'expenses'             => 'description',
            default                => 'id',
        };

        $rows = $db->table($meta['table'])
            ->select("id, {$labelCol} as display_label")
            ->orderBy('id', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        $ids = array_column($rows, 'id');
        $map = $this->mappingModel->getMapByLocalIds($module, $ids);

        foreach ($rows as &$r) {
            $id = (int) $r['id'];
            $r['sync_status']    = $map[$id]['sync_status'] ?? 'NOT_SYNCED';
            $r['zoho_record_id'] = $map[$id]['zoho_record_id'] ?? '';
        }

        return $this->response->setJSON(['success' => true, 'records' => $rows]);
    }

    /**
     * Conflict Management Screen
     */
    public function conflicts()
    {
        $statusFilter = $this->request->getGet('status') ?: 'OPEN';
        $moduleFilter = $this->request->getGet('module') ?: '';

        $builder = $this->conflictModel->orderBy('id', 'DESC');
        if ($statusFilter !== 'ALL') {
            $builder->where('status', $statusFilter);
        }
        if ($moduleFilter !== '') {
            $builder->where('module', $moduleFilter);
        }

        $conflicts = $builder->findAll(100);

        return view('zoho/conflicts', [
            'title'        => 'Zoho Synchronization Conflicts',
            'conflicts'    => $conflicts,
            'statusFilter' => $statusFilter,
            'moduleFilter' => $moduleFilter,
            'modulesList'  => ZohoSyncOrchestrator::ORDERED_MODULES,
        ]);
    }

    /**
     * Resolve Conflict Action (Choose Local Version | Choose Zoho Version | Ignore)
     */
    public function resolveConflict(int $id)
    {
        $choice = (string) ($this->request->getPost('choice') ?: $this->request->getGet('choice'));
        $notes  = (string) $this->request->getPost('notes');
        $userId = (int) (session('user_id') ?? 0) ?: null;

        if (strtoupper($choice) === 'IGNORE') {
            $this->conflictModel->update($id, [
                'status'           => 'IGNORED',
                'resolved_by'      => $userId,
                'resolved_at'      => date('Y-m-d H:i:s'),
                'resolution_notes' => $notes ?: 'Marked ignored by user.',
            ]);
            return redirect()->to('settings/integrations/zoho/conflicts')->with('success', 'Conflict marked as ignored.');
        }

        $res = $this->orchestrator->resolveConflict($id, $choice, $userId, $notes);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($res);
        }

        if (!empty($res['success'])) {
            return redirect()->to('settings/integrations/zoho/conflicts')->with('success', $res['message']);
        }
        return redirect()->to('settings/integrations/zoho/conflicts')->with('error', $res['message'] ?? 'Failed to resolve conflict.');
    }

    /**
     * Action G: Reconciliation Report Screen
     */
    public function reconciliation()
    {
        $live = $this->request->getGet('live') !== '0';
        $userId = (int) (session('user_id') ?? 0) ?: null;

        $reconService = new ReconciliationService();
        $report       = $reconService->runReconciliation($live, $userId);

        return view('zoho/reconciliation', [
            'title'  => 'Zoho Books vs CI4 ERP Reconciliation',
            'report' => $report,
        ]);
    }

    /**
     * AJAX endpoint to inspect full payload (reads from JSON file on disk if offloaded)
     */
    public function logPayload(int $logId)
    {
        $data = $this->logModel->getFullPayload($logId);
        return $this->response->setJSON($data);
    }

    /**
     * Clean logs older than 90 days
     */
    public function pruneOldLogs()
    {
        $days = (int) ($this->request->getPost('days') ?? 90);
        $deleted = $this->logModel->pruneLogsOlderThan(max(7, $days));
        return redirect()->back()->with('success', "Cleaned {$deleted} log records and associated JSON files older than {$days} days.");
    }

    /**
     * Sync Logs & Queue Inspector Screen
     */
    public function logs()
    {
        $moduleFilter    = (string) ($this->request->getGet('module') ?? '');
        $statusFilter    = (string) ($this->request->getGet('status') ?? '');
        $directionFilter = (string) ($this->request->getGet('direction') ?? '');

        $builder = $this->logModel->orderBy('id', 'DESC');
        if ($moduleFilter !== '') {
            $builder->where('module', $moduleFilter);
        }
        if ($statusFilter !== '') {
            $builder->where('status', $statusFilter);
        }
        if ($directionFilter !== '') {
            $builder->where('direction', $directionFilter);
        }

        $logs       = $builder->findAll(150);
        $queueItems = $this->queueModel->orderBy('id', 'DESC')->findAll(50);

        return view('zoho/logs', [
            'title'           => 'Zoho Synchronization Audit Logs & Queue',
            'logs'            => $logs,
            'queueItems'      => $queueItems,
            'moduleFilter'    => $moduleFilter,
            'statusFilter'    => $statusFilter,
            'directionFilter' => $directionFilter,
            'modulesList'     => ZohoSyncOrchestrator::ORDERED_MODULES,
        ]);
    }

    protected function respondSyncResult(array $res, string $defaultSuccessTitle)
    {
        $failedCount = (int) ($res['failed_count'] ?? 0);
        $summaryMsg = sprintf(
            '%s — Processed: %d | Created: %d | Updated: %d | Skipped: %d | Conflicts: %d | Failed: %d',
            $defaultSuccessTitle,
            (int) ($res['total_processed'] ?? 0),
            (int) ($res['created_count'] ?? 0),
            (int) ($res['updated_count'] ?? 0),
            (int) ($res['skipped_count'] ?? 0),
            (int) ($res['conflict_count'] ?? 0),
            $failedCount
        );

        if (!empty($res['message'])) {
            $summaryMsg .= ' (' . $res['message'] . ')';
        }

        if ($this->request->isAJAX()) {
            $res['summary_message'] = $summaryMsg;
            return $this->response->setJSON($res);
        }

        if (!empty($res['success']) && $failedCount === 0) {
            return redirect()->to('settings/integrations/zoho/sync')->with('success', $summaryMsg);
        }

        // If all modules failed or an explicit error occurred
        return redirect()->to('settings/integrations/zoho/sync')->with('error', $summaryMsg);
    }
}

