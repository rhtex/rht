<?php

namespace App\Services\Zoho\Modules;

use App\Models\VendorCreditModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class VendorCreditSyncService extends AbstractModuleSyncService
{
    protected VendorCreditModel $creditModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->creditModel = new VendorCreditModel();
    }

    public function getModuleName(): string
    {
        return 'vendor_credits';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('vendor_credits', $localId);
        $credit  = $this->creditModel->find($localId);

        if ($operation === 'DELETE' || $operation === 'VOID' || !$credit) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Vendor Credit was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/vendorcredits/{$zohoId}", [], [
                'module'          => 'vendor_credits',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('vendor_credits', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Vendor Credit deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('vendor_credits', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $normalizedLocal = $this->normalizeLocalCredit($credit);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Vendor Credit unchanged since last sync.',
                ];
            }
        }

        $zohoVendorId = $this->ensureZohoVendorId((int) $credit['vendor_id']);
        if (empty($zohoVendorId)) {
            $msg = 'Unable to sync Vendor #' . $credit['vendor_id'] . ' to Zoho Books for Vendor Credit.';
            $this->mappingModel->markStatus('vendor_credits', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId    = $mapping['zoho_record_id'] ?? null;
        $metaCache = $this->taxMapper->ensureMetadataCache();
        $accId     = $metaCache['default_purchase_account_id'] ?? null;

        $refNo   = trim((string) ($credit['reference_no'] ?: ('VCN-' . str_pad((string) $localId, 4, '0', STR_PAD_LEFT))));
        $companyState = $this->taxMapper->getCompanyStateCode();
        $sourceState  = $this->resolveVendorSourceOfSupply((int) $credit['vendor_id'], false);

        $lineItem = [
            'description' => (string) ($credit['notes'] ?: 'Vendor Return / Credit Note'),
            'quantity'    => 1,
            'rate'        => round((float) ($credit['amount'] ?? 0), 2),
        ];
        if ($accId) {
            $lineItem['account_id'] = $accId;
        }

        $payload = [
            'vendor_id'             => $zohoVendorId,
            'vendor_credit_number'  => $refNo,
            'date'                  => !empty($credit['created_at']) ? date('Y-m-d', strtotime($credit['created_at'])) : date('Y-m-d'),
            'source_of_supply'      => $sourceState,
            'destination_of_supply' => $companyState,
            'line_items'            => [$lineItem],
            'notes'                 => (string) ($credit['notes'] ?? ''),
        ];

        $query = ['ignore_auto_number_generation' => 'true'];

        if (!empty($zohoId)) {
            $res = $this->client->put("/vendorcredits/{$zohoId}", $payload, $query, [
                'module'          => 'vendor_credits',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/vendorcredits', $payload, $query, [
                'module'          => 'vendor_credits',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('vendor_credits', $localId, 'FAILED', $res['message'] ?? 'Vendor Credit sync failed');
            return $res;
        }

        $zohoVc         = $res['data']['vendor_credit'] ?? [];
        $newZohoId      = (string) ($zohoVc['vendor_credit_id'] ?? $zohoId);
        $normalizedZoho = !empty($zohoVc) ? $this->normalizeZohoCredit($zohoVc) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $refNo,
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoVc['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Vendor Credit synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('vendor_credits');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/vendorcredits', 'vendor_credits', $query, 50, [
            'module'    => 'vendor_credits',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_VENDOR_CREDITS',
        ]);

        if (empty($listRes['success'])) {
            return $listRes;
        }

        $stats = [
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'skipped_count'   => 0,
            'conflict_count'  => 0,
            'failed_count'    => 0,
        ];
        $latestModified = null;

        foreach ($listRes['records'] as $summary) {
            $stats['total_processed']++;
            $applyRes = $this->applyZohoRecordToLocal($summary, !$checkConflicts);
            $act      = $applyRes['action'] ?? 'SKIPPED';
            if ($act === 'CREATED') {
                $stats['created_count']++;
            } elseif ($act === 'UPDATED') {
                $stats['updated_count']++;
            } elseif ($act === 'CONFLICT') {
                $stats['conflict_count']++;
            } elseif ($act === 'FAILED') {
                $stats['failed_count']++;
            } else {
                $stats['skipped_count']++;
            }

            if (!empty($summary['last_modified_time'])) {
                $latestModified = $summary['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('vendor_credits', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoVc, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoVc['vendor_credit_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho vendor_credit_id'];
        }

        $mapping     = $this->mappingModel->findByZoho('vendor_credits', $zohoId);
        $localCredit = null;

        if ($mapping) {
            $localCredit = $this->creditModel->find((int) $mapping['local_record_id']);
        }
        if (!$localCredit && !empty($zohoVc['vendor_credit_number'])) {
            $localCredit = $this->creditModel->where('reference_no', trim($zohoVc['vendor_credit_number']))->first();
        }

        $normalizedZoho = $this->normalizeZohoCredit($zohoVc);

        if ($localCredit && !$forceOverwrite && $mapping) {
            $localId         = (int) $localCredit['id'];
            $normalizedLocal = $this->normalizeLocalCredit($localCredit);

            $eval = $this->conflictService->evaluate(
                'vendor_credits',
                $localId,
                $zohoId,
                $localCredit['reference_no'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localCredit['updated_at'] ?? null,
                $zohoVc['last_modified_time'] ?? null,
                $mapping
            );

            if ($eval['action'] === 'NO_CHANGE') {
                return ['success' => true, 'action' => 'SKIPPED', 'local_record_id' => $localId];
            }
            if ($eval['action'] === 'CONFLICT') {
                return ['success' => false, 'action' => 'CONFLICT', 'local_record_id' => $localId, 'conflict_id' => $eval['conflict_id']];
            }
            if ($eval['action'] === 'APPLY_LOCAL') {
                $this->pushToZoho($localId, 'UPDATE');
                return ['success' => true, 'action' => 'UPDATED', 'local_record_id' => $localId];
            }
        }

        $localVendorId = $this->ensureLocalVendorId((string) ($zohoVc['vendor_id'] ?? ''), $zohoVc['vendor_name'] ?? null);
        if (!$localVendorId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local vendor for Zoho Vendor Credit ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoVc, $zohoId, $localCredit, $localVendorId, $normalizedZoho) {
            $total   = (float) ($zohoVc['total'] ?? 0);
            $balance = (float) ($zohoVc['balance'] ?? $total);
            $used    = max(0.0, round($total - $balance, 2));

            $status = 'Unused';
            if ($balance <= 0.009 && $total > 0) {
                $status = 'Used';
            } elseif ($used > 0) {
                $status = 'Partial';
            }

            $vcData = [
                'vendor_id'    => $localVendorId,
                'amount'       => $total,
                'reference_no' => trim((string) ($zohoVc['vendor_credit_number'] ?? ($localCredit['reference_no'] ?? ('VCN-' . substr($zohoId, -5))))),
                'notes'        => $zohoVc['notes'] ?? ($localCredit['notes'] ?? 'Zoho Vendor Credit'),
                'status'       => $status,
                'used_amount'  => $used,
            ];

            if ($localCredit) {
                $localId = (int) $localCredit['id'];
                $this->creditModel->skipValidation(true)->update($localId, $vcData);
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->creditModel->skipValidation(true)->insert($vcData, true);
                $action = 'CREATED';
            }

            $updated         = $this->creditModel->find($localId);
            $normalizedLocal = $this->normalizeLocalCredit($updated);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['reference_no'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoVc['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    public function normalizeLocalCredit(array $c): array
    {
        return [
            'reference_no' => trim((string) ($c['reference_no'] ?? '')),
            'amount'       => round((float) ($c['amount'] ?? 0), 2),
        ];
    }

    public function normalizeZohoCredit(array $z): array
    {
        return [
            'reference_no' => trim((string) ($z['vendor_credit_number'] ?? '')),
            'amount'       => round((float) ($z['total'] ?? 0), 2),
        ];
    }
}

