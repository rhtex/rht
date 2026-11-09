<?php

namespace App\Services\Zoho\Modules;

use App\Models\AccountingModel;
use App\Models\BillItemModel;
use App\Models\BillModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class BillSyncService extends AbstractModuleSyncService
{
    protected BillModel $billModel;
    protected BillItemModel $itemModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->billModel = new BillModel();
        $this->itemModel = new BillItemModel();
    }

    public function getModuleName(): string
    {
        return 'bills';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('bills', $localId);
        $bill    = $this->billModel->getBillById($localId);

        if ($operation === 'DELETE' || $operation === 'VOID' || !$bill || ($bill['status'] ?? '') === 'Void') {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Bill was not synced to Zoho; nothing to delete/void.'];
            }
            $zohoId = $mapping['zoho_record_id'];

            if ($bill && (float) ($bill['paid_amount'] ?? 0) > 0) {
                $msg = 'Cannot delete or void Purchase Bill #' . $bill['bill_number'] . ' in Zoho Books because vendor payments are applied. Please delete or unlink the payment first.';
                $this->mappingModel->markStatus('bills', $localId, 'FAILED', $msg);
                return ['success' => false, 'message' => $msg];
            }

            if ($operation === 'VOID' || ($bill && ($bill['status'] ?? '') === 'Void')) {
                $voidRes = $this->client->post("/bills/{$zohoId}/status/void", [], [], [
                    'module'          => 'bills',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $zohoId,
                    'operation'       => 'VOID',
                ]);
                if (!empty($voidRes['success'])) {
                    $this->mappingModel->markStatus('bills', $localId, 'VOIDED');
                    return ['success' => true, 'message' => 'Purchase Bill voided in Zoho Books.'];
                }
                $this->mappingModel->markStatus('bills', $localId, 'FAILED', $voidRes['message'] ?? 'Void failed');
                return $voidRes;
            }

            $delRes = $this->client->delete("/bills/{$zohoId}", [], [
                'module'          => 'bills',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('bills', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Purchase Bill deleted in Zoho Books.'];
            }

            $voidFallback = $this->client->post("/bills/{$zohoId}/status/void", [], [], [
                'module'          => 'bills',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'VOID_FALLBACK',
            ]);
            if (!empty($voidFallback['success'])) {
                $this->mappingModel->markStatus('bills', $localId, 'VOIDED');
                return ['success' => true, 'message' => 'Purchase Bill voided in Zoho Books.'];
            }

            $this->mappingModel->markStatus('bills', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $items           = $bill['items'] ?? [];
        $normalizedLocal = $this->normalizeLocalBill($bill, $items);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Purchase Bill unchanged since last sync.',
                ];
            }
        }

        $zohoVendorId = $this->ensureZohoVendorId((int) $bill['vendor_id']);
        if (empty($zohoVendorId)) {
            $msg = 'Unable to sync Supplier #' . $bill['vendor_id'] . ' to Zoho Books for Bill #' . $bill['bill_number'];
            $this->mappingModel->markStatus('bills', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId) && !empty($bill['bill_number'])) {
            $lookup = $this->client->get('/bills', [
                'bill_number' => $bill['bill_number'],
                'vendor_id'   => $zohoVendorId,
            ], ['module' => 'bills', 'operation' => 'LOOKUP_BY_NUMBER']);
            if (!empty($lookup['data']['bills'][0]['bill_id'])) {
                $zohoId = (string) $lookup['data']['bills'][0]['bill_id'];
            }
        }

        $payload = $this->buildZohoPayload($bill, $items, $zohoVendorId);

        if (!empty($zohoId)) {
            $res = $this->client->put("/bills/{$zohoId}", $payload, [], [
                'module'          => 'bills',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/bills', $payload, [], [
                'module'          => 'bills',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('bills', $localId, 'FAILED', $res['message'] ?? 'Bill sync failed');
            return $res;
        }

        $zohoBill  = $res['data']['bill'] ?? [];
        $newZohoId = (string) ($zohoBill['bill_id'] ?? $zohoId);

        if (($bill['status'] ?? 'Open') !== 'Draft' && strtolower((string) ($zohoBill['status'] ?? 'draft')) === 'draft' && !empty($newZohoId)) {
            $this->client->post("/bills/{$newZohoId}/status/open", [], [], [
                'module'          => 'bills',
                'local_record_id' => $localId,
                'zoho_record_id'  => $newZohoId,
                'operation'       => 'MARK_OPEN',
            ]);
            $zohoBill['status'] = 'open';
        }

        $normalizedZoho = !empty($zohoBill) ? $this->normalizeZohoBill($zohoBill) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $bill['bill_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoBill['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Purchase Bill synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('bills');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/bills', 'bills', $query, 50, [
            'module'    => 'bills',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_BILLS',
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
            $zohoId = (string) ($summary['bill_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('bills', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            $detailRes = $this->client->get("/bills/{$zohoId}", [], [
                'module'         => 'bills',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_BILL_DETAIL',
            ]);

            $zohoBill = !empty($detailRes['success']) ? ($detailRes['data']['bill'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoBill, !$checkConflicts);
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

            if (!empty($zohoBill['last_modified_time'])) {
                $latestModified = $zohoBill['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('bills', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoBill, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoBill['bill_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho bill_id'];
        }

        $mapping   = $this->mappingModel->findByZoho('bills', $zohoId);
        $localBill = null;

        if ($mapping) {
            $localBill = $this->billModel->getBillById((int) $mapping['local_record_id']);
        }
        if (!$localBill && !empty($zohoBill['bill_number'])) {
            $found = $this->billModel->where('bill_number', trim($zohoBill['bill_number']))->first();
            if ($found) {
                $localBill = $this->billModel->getBillById((int) $found['id']);
            }
        }

        $normalizedZoho = $this->normalizeZohoBill($zohoBill);

        if ($localBill && !$forceOverwrite && $mapping) {
            $localId         = (int) $localBill['id'];
            $normalizedLocal = $this->normalizeLocalBill($localBill, $localBill['items'] ?? []);

            $eval = $this->conflictService->evaluate(
                'bills',
                $localId,
                $zohoId,
                $localBill['bill_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localBill['updated_at'] ?? null,
                $zohoBill['last_modified_time'] ?? null,
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

        $localVendorId = $this->ensureLocalVendorId((string) ($zohoBill['vendor_id'] ?? ''), $zohoBill['vendor_name'] ?? null);
        if (!$localVendorId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local vendor for Zoho Bill ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoBill, $zohoId, $localBill, $localVendorId, $normalizedZoho) {
            $companyState   = $this->taxMapper->getCompanyStateCode();
            $sourceOfSupply = strtoupper(trim((string) ($zohoBill['source_of_supply'] ?? $companyState)));
            $isInterState   = ($sourceOfSupply !== '' && $sourceOfSupply !== $companyState) ? 1 : 0;

            $taxTotal  = (float) ($zohoBill['tax_total'] ?? 0);
            $cgstTotal = $isInterState ? 0.00 : round($taxTotal / 2, 2);
            $sgstTotal = $isInterState ? 0.00 : round($taxTotal - $cgstTotal, 2);
            $igstTotal = $isInterState ? round($taxTotal, 2) : 0.00;

            $total   = (float) ($zohoBill['total'] ?? 0);
            $balance = (float) ($zohoBill['balance'] ?? 0);
            $paid    = max(0.00, round($total - $balance, 2));

            $zohoStatus = strtolower((string) ($zohoBill['status'] ?? 'open'));
            $statusMap  = [
                'draft'          => 'Draft',
                'open'           => 'Open',
                'overdue'        => 'Overdue',
                'paid'           => 'Paid',
                'partially_paid' => 'Partially Paid',
                'void'           => 'Void',
            ];
            $localStatus = $statusMap[$zohoStatus] ?? 'Open';

            $billData = [
                'vendor_id'        => $localVendorId,
                'bill_number'      => trim((string) ($zohoBill['bill_number'] ?? ($localBill['bill_number'] ?? $this->billModel->generateBillNumber()))),
                'bill_date'        => $zohoBill['date'] ?? date('Y-m-d'),
                'due_date'         => $zohoBill['due_date'] ?? ($zohoBill['date'] ?? date('Y-m-d')),
                'reference_number' => $zohoBill['reference_number'] ?? null,
                'status'           => $localStatus,
                'is_inter_state'   => $isInterState,
                'subtotal'         => (float) ($zohoBill['sub_total'] ?? 0),
                'discount_amount'  => (float) ($zohoBill['discount_total'] ?? $zohoBill['discount'] ?? 0),
                'discount_type'    => 'Amount',
                'shipping_charge'  => (float) ($zohoBill['shipping_charge'] ?? 0),
                'roundoff_amount'  => (float) ($zohoBill['adjustment'] ?? 0),
                'cgst_amount'      => $cgstTotal,
                'sgst_amount'      => $sgstTotal,
                'igst_amount'      => $igstTotal,
                'tax_amount'       => $taxTotal,
                'total_amount'     => $total,
                'paid_amount'      => $paid,
                'balance'          => $balance,
                'notes'            => $zohoBill['notes'] ?? null,
                'terms'            => $zohoBill['terms'] ?? null,
            ];

            if ($localBill) {
                $localId = (int) $localBill['id'];
                $this->billModel->skipValidation(true)->update($localId, $billData);
                $this->itemModel->where('bill_id', $localId)->delete();
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->billModel->skipValidation(true)->insert($billData, true);
                $action = 'CREATED';
            }

            foreach (($zohoBill['line_items'] ?? []) as $li) {
                $prodId   = $this->ensureLocalProductId($li['item_id'] ?? null, $li['name'] ?? null, $li['sku'] ?? null, (float) ($li['rate'] ?? 0), $li['hsn_or_sac'] ?? null);
                $taxRates = $this->taxMapper->extractLineTaxRates($li, (bool) $isInterState);
                $qty      = (float) ($li['quantity'] ?? 1);
                $rate     = (float) ($li['rate'] ?? 0);

                $this->itemModel->insert([
                    'bill_id'        => $localId,
                    'product_id'     => $prodId,
                    'description'    => $li['description'] ?? ($li['name'] ?? ''),
                    'hsn_code'       => $li['hsn_or_sac'] ?? null,
                    'quantity'       => $qty,
                    'rate'           => $rate,
                    'tax_id'         => $this->taxMapper->resolveLocalTaxId($taxRates['tax_percentage']),
                    'tax_percentage' => $taxRates['tax_percentage'],
                    'cgst_rate'      => $taxRates['cgst_rate'],
                    'sgst_rate'      => $taxRates['sgst_rate'],
                    'igst_rate'      => $taxRates['igst_rate'],
                    'amount'         => (float) ($li['item_total'] ?? ($qty * $rate)),
                ]);
            }

            if ($action === 'CREATED' && !in_array($localStatus, ['Draft', 'Void'], true)) {
                try {
                    $accountingModel = new AccountingModel();
                    $accountingModel->recordTransaction('bill', $localId, $billData['bill_date'], 'Zoho Bill #' . $billData['bill_number'], [
                        ['account_name' => 'Cost of Purchase', 'debit' => round($total - $taxTotal, 2), 'credit' => 0],
                        ['account_name' => 'GST Receivable', 'debit' => $taxTotal, 'credit' => 0],
                        ['account_name' => 'Accounts Payable', 'debit' => 0, 'credit' => $total],
                    ]);
                } catch (\Throwable $e) {
                    log_message('error', 'BillSyncService ledger post error: ' . $e->getMessage());
                }
            }

            $updated         = $this->billModel->getBillById($localId);
            $normalizedLocal = $this->normalizeLocalBill($updated, $updated['items'] ?? []);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['bill_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoBill['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $bill, array $items, string $zohoVendorId): array
    {
        $isInterState   = !empty($bill['is_inter_state']);
        $companyState   = $this->taxMapper->getCompanyStateCode();
        $sourceOfSupply = $this->resolveVendorSourceOfSupply((int) $bill['vendor_id'], $isInterState);
        $metaCache      = $this->taxMapper->ensureMetadataCache();
        $purchaseAccId  = $metaCache['default_purchase_account_id'] ?? null;

        $lineItems = [];
        foreach ($items as $item) {
            $zohoItemId = $this->ensureZohoItemId(!empty($item['product_id']) ? (int) $item['product_id'] : null);
            $taxPct     = (float) ($item['tax_percentage'] ?? 0);
            $zohoTaxId  = $this->taxMapper->resolveZohoTaxId($taxPct, $isInterState);

            $li = [
                'description' => (string) ($item['description'] ?? ''),
                'quantity'    => (float) ($item['quantity'] ?? 1),
                'rate'        => round((float) ($item['rate'] ?? 0), 2),
            ];
            if ($zohoItemId) {
                $li['item_id'] = $zohoItemId;
            } else {
                $li['name'] = (string) ($item['description'] ?: 'Bill Item');
            }
            if ($purchaseAccId) {
                $li['account_id'] = $purchaseAccId;
            }
            if (!empty($item['hsn_code'])) {
                $li['hsn_or_sac'] = trim((string) $item['hsn_code']);
            }
            if ($zohoTaxId) {
                $li['tax_id'] = $zohoTaxId;
            }
            $lineItems[] = $li;
        }

        $discountVal = (float) ($bill['discount_amount'] ?? 0);
        $discountStr = ($bill['discount_type'] ?? 'Amount') === 'Percentage'
            ? $discountVal . '%'
            : round($discountVal, 2);

        return [
            'vendor_id'              => $zohoVendorId,
            'bill_number'            => $bill['bill_number'],
            'date'                   => $bill['bill_date'],
            'due_date'               => $bill['due_date'] ?: $bill['bill_date'],
            'source_of_supply'       => $sourceOfSupply,
            'destination_of_supply'  => $companyState,
            'reference_number'       => (string) ($bill['reference_number'] ?? ''),
            'line_items'             => $lineItems,
            'discount'               => $discountStr,
            'is_discount_before_tax' => true,
            'discount_type'          => 'entity_level',
            'adjustment'             => round((float) ($bill['roundoff_amount'] ?? 0) + (float) ($bill['shipping_charge'] ?? 0), 2),
            'adjustment_description' => 'Adjustment / Shipping',
            'notes'                  => (string) ($bill['notes'] ?? ''),
            'terms'                  => (string) ($bill['terms'] ?? ''),
        ];
    }

    public function normalizeLocalBill(array $b, array $items): array
    {
        return [
            'bill_number'     => trim((string) ($b['bill_number'] ?? '')),
            'date'            => (string) ($b['bill_date'] ?? ''),
            'due_date'        => (string) ($b['due_date'] ?? ''),
            'subtotal'        => round((float) ($b['subtotal'] ?? 0), 2),
            'discount_amount' => round((float) ($b['discount_amount'] ?? 0), 2),
            'tax_amount'      => round((float) ($b['tax_amount'] ?? 0), 2),
            'total_amount'    => round((float) ($b['total_amount'] ?? 0), 2),
            'items_count'     => count($items),
        ];
    }

    public function normalizeZohoBill(array $z): array
    {
        return [
            'bill_number'     => trim((string) ($z['bill_number'] ?? '')),
            'date'            => (string) ($z['date'] ?? ''),
            'due_date'        => (string) ($z['due_date'] ?? ''),
            'subtotal'        => round((float) ($z['sub_total'] ?? 0), 2),
            'discount_amount' => round((float) ($z['discount_total'] ?? $z['discount'] ?? 0), 2),
            'tax_amount'      => round((float) ($z['tax_total'] ?? 0), 2),
            'total_amount'    => round((float) ($z['total'] ?? 0), 2),
            'items_count'     => isset($z['line_items']) && is_array($z['line_items']) ? count($z['line_items']) : 0,
        ];
    }
}

