<?php

namespace App\Services\Zoho\Modules;

use App\Models\PurchaseOrderItemModel;
use App\Models\PurchaseOrderModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class PurchaseOrderSyncService extends AbstractModuleSyncService
{
    protected PurchaseOrderModel $poModel;
    protected PurchaseOrderItemModel $itemModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->poModel   = new PurchaseOrderModel();
        $this->itemModel = new PurchaseOrderItemModel();
    }

    public function getModuleName(): string
    {
        return 'purchase_orders';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('purchase_orders', $localId);
        $po      = $this->poModel->getOrderById($localId);

        if ($operation === 'DELETE' || $operation === 'VOID' || !$po) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Purchase Order was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/purchaseorders/{$zohoId}", [], [
                'module'          => 'purchase_orders',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('purchase_orders', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Purchase Order deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('purchase_orders', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $items           = $po['items'] ?? [];
        $normalizedLocal = $this->normalizeLocalPo($po, $items);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Purchase Order unchanged since last sync.',
                ];
            }
        }

        $zohoVendorId = $this->ensureZohoVendorId((int) $po['vendor_id']);
        if (empty($zohoVendorId)) {
            $msg = 'Unable to sync Vendor #' . $po['vendor_id'] . ' to Zoho Books for Purchase Order.';
            $this->mappingModel->markStatus('purchase_orders', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId) && !empty($po['purchase_order_number'])) {
            $lookup = $this->client->get('/purchaseorders', ['purchaseorder_number' => $po['purchase_order_number']], [
                'module'    => 'purchase_orders',
                'operation' => 'LOOKUP_BY_NUMBER',
            ]);
            if (!empty($lookup['data']['purchaseorders'][0]['purchaseorder_id'])) {
                $zohoId = (string) $lookup['data']['purchaseorders'][0]['purchaseorder_id'];
            }
        }

        $payload = $this->buildZohoPayload($po, $items, $zohoVendorId);
        $query   = ['ignore_auto_number_generation' => 'true'];

        if (!empty($zohoId)) {
            $res = $this->client->put("/purchaseorders/{$zohoId}", $payload, $query, [
                'module'          => 'purchase_orders',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/purchaseorders', $payload, $query, [
                'module'          => 'purchase_orders',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('purchase_orders', $localId, 'FAILED', $res['message'] ?? 'Purchase Order sync failed');
            return $res;
        }

        $zohoPo         = $res['data']['purchaseorder'] ?? [];
        $newZohoId      = (string) ($zohoPo['purchaseorder_id'] ?? $zohoId);
        $normalizedZoho = !empty($zohoPo) ? $this->normalizeZohoPo($zohoPo) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $po['purchase_order_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoPo['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Purchase Order synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('purchase_orders');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/purchaseorders', 'purchaseorders', $query, 50, [
            'module'    => 'purchase_orders',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_PURCHASE_ORDERS',
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
        $totalRecords = count($listRes['records']);
        $idx = 0;
        $this->emitProgress(15, "Retrieved {$totalRecords} purchase order(s) from Zoho Books. Starting import...", ['total' => $totalRecords]);

        foreach ($listRes['records'] as $summary) {
            $idx++;
            $stats['total_processed']++;
            $zohoId = (string) ($summary['purchaseorder_id'] ?? '');
            $poNum  = $summary['purchaseorder_number'] ?? $zohoId;
            $pct    = 15 + (int) round(($idx / max(1, $totalRecords)) * 80);

            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('purchase_orders', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Skipped unchanged PO #{$poNum}", ['processed' => $idx, 'total' => $totalRecords]);
                continue;
            }

            $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Importing PO #{$poNum}...", ['processed' => $idx, 'total' => $totalRecords]);

            $detailRes = $this->client->get("/purchaseorders/{$zohoId}", [], [
                'module'         => 'purchase_orders',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_PURCHASE_ORDER_DETAIL',
            ]);

            $zohoPo   = !empty($detailRes['success']) ? ($detailRes['data']['purchaseorder'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoPo, !$checkConflicts);
            $act      = $applyRes['action'] ?? 'SKIPPED';
            if ($act === 'CREATED') {
                $stats['created_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Created PO #{$poNum}", ['action' => 'CREATED']);
            } elseif ($act === 'UPDATED') {
                $stats['updated_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Updated PO #{$poNum}", ['action' => 'UPDATED']);
            } elseif ($act === 'CONFLICT') {
                $stats['conflict_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Conflict detected on PO #{$poNum}", ['action' => 'CONFLICT']);
            } elseif ($act === 'FAILED') {
                $stats['failed_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Failed PO #{$poNum}: " . ($applyRes['message'] ?? 'Error'), ['action' => 'FAILED']);
            } else {
                $stats['skipped_count']++;
            }

            if (!empty($zohoPo['last_modified_time'])) {
                $latestModified = $zohoPo['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('purchase_orders', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoPo, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoPo['purchaseorder_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho purchaseorder_id'];
        }

        $mapping = $this->mappingModel->findByZoho('purchase_orders', $zohoId);
        $localPo = null;

        if ($mapping) {
            $localPo = $this->poModel->getOrderById((int) $mapping['local_record_id']);
        }
        if (!$localPo && !empty($zohoPo['purchaseorder_number'])) {
            $found = $this->poModel->where('purchase_order_number', trim($zohoPo['purchaseorder_number']))->first();
            if ($found) {
                $localPo = $this->poModel->getOrderById((int) $found['id']);
            }
        }

        $normalizedZoho = $this->normalizeZohoPo($zohoPo);

        if ($localPo && !$forceOverwrite && $mapping) {
            $localId         = (int) $localPo['id'];
            $normalizedLocal = $this->normalizeLocalPo($localPo, $localPo['items'] ?? []);

            $eval = $this->conflictService->evaluate(
                'purchase_orders',
                $localId,
                $zohoId,
                $localPo['purchase_order_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localPo['updated_at'] ?? null,
                $zohoPo['last_modified_time'] ?? null,
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

        $localVendorId = $this->ensureLocalVendorId((string) ($zohoPo['vendor_id'] ?? ''), $zohoPo['vendor_name'] ?? null);
        if (!$localVendorId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local vendor for Zoho Purchase Order ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoPo, $zohoId, $localPo, $localVendorId, $normalizedZoho) {
            $companyState   = $this->taxMapper->getCompanyStateCode();
            $sourceOfSupply = strtoupper(trim((string) ($zohoPo['source_of_supply'] ?? $companyState)));
            $isInterState   = ($sourceOfSupply !== '' && $sourceOfSupply !== $companyState) ? 1 : 0;

            $taxTotal  = (float) ($zohoPo['tax_total'] ?? 0);
            $cgstTotal = $isInterState ? 0.00 : round($taxTotal / 2, 2);
            $sgstTotal = $isInterState ? 0.00 : round($taxTotal - $cgstTotal, 2);
            $igstTotal = $isInterState ? round($taxTotal, 2) : 0.00;

            $statusMap = [
                'draft'     => 'Draft',
                'open'      => 'Issued',
                'issued'    => 'Issued',
                'billed'    => 'Billed',
                'cancelled' => 'Cancelled',
                'closed'    => 'Closed',
            ];
            $zStatus = strtolower((string) ($zohoPo['status'] ?? 'issued'));

            $poData = [
                'vendor_id'             => $localVendorId,
                'purchase_order_number' => trim((string) ($zohoPo['purchaseorder_number'] ?? ($localPo['purchase_order_number'] ?? $this->poModel->generateOrderNumber()))),
                'order_date'            => $zohoPo['date'] ?? date('Y-m-d'),
                'delivery_date'         => $zohoPo['delivery_date'] ?? null,
                'reference_number'      => $zohoPo['reference_number'] ?? null,
                'status'                => $statusMap[$zStatus] ?? 'Issued',
                'is_inter_state'        => $isInterState,
                'subtotal'              => (float) ($zohoPo['sub_total'] ?? 0),
                'discount_amount'       => (float) ($zohoPo['discount_total'] ?? $zohoPo['discount'] ?? 0),
                'discount_type'         => 'Amount',
                'shipping_charge'       => (float) ($zohoPo['shipping_charge'] ?? 0),
                'roundoff_amount'       => (float) ($zohoPo['adjustment'] ?? 0),
                'cgst_amount'           => $cgstTotal,
                'sgst_amount'           => $sgstTotal,
                'igst_amount'           => $igstTotal,
                'tax_amount'            => $taxTotal,
                'total_amount'          => (float) ($zohoPo['total'] ?? 0),
                'notes'                 => $zohoPo['notes'] ?? null,
                'terms'                 => $zohoPo['terms'] ?? null,
            ];

            if ($localPo) {
                $localId = (int) $localPo['id'];
                $this->poModel->skipValidation(true)->update($localId, $poData);
                $this->itemModel->where('purchase_order_id', $localId)->delete();
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->poModel->skipValidation(true)->insert($poData, true);
                $action = 'CREATED';
            }

            foreach (($zohoPo['line_items'] ?? []) as $li) {
                $prodId   = $this->ensureLocalProductId($li['item_id'] ?? null, $li['name'] ?? null, $li['sku'] ?? null, (float) ($li['rate'] ?? 0), $li['hsn_or_sac'] ?? null);
                $taxRates = $this->taxMapper->extractLineTaxRates($li, (bool) $isInterState);
                $qty      = (float) ($li['quantity'] ?? 1);
                $rate     = (float) ($li['rate'] ?? 0);

                $this->itemModel->insert([
                    'purchase_order_id' => $localId,
                    'product_id'        => $prodId,
                    'description'       => $li['description'] ?? ($li['name'] ?? ''),
                    'hsn_code'          => $li['hsn_or_sac'] ?? null,
                    'quantity'          => $qty,
                    'rate'              => $rate,
                    'tax_percentage'    => $taxRates['tax_percentage'],
                    'cgst_rate'         => $taxRates['cgst_rate'],
                    'sgst_rate'         => $taxRates['sgst_rate'],
                    'igst_rate'         => $taxRates['igst_rate'],
                    'amount'            => (float) ($li['item_total'] ?? ($qty * $rate)),
                ]);
            }

            $updated         = $this->poModel->getOrderById($localId);
            $normalizedLocal = $this->normalizeLocalPo($updated, $updated['items'] ?? []);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['purchase_order_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoPo['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $po, array $items, string $zohoVendorId): array
    {
        $isInterState   = !empty($po['is_inter_state']);
        $companyState   = $this->taxMapper->getCompanyStateCode();
        $sourceOfSupply = $this->resolveVendorSourceOfSupply((int) $po['vendor_id'], $isInterState);

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
                $li['name'] = (string) ($item['description'] ?: 'PO Item');
            }
            if (!empty($item['hsn_code'])) {
                $li['hsn_or_sac'] = trim((string) $item['hsn_code']);
            }
            if ($zohoTaxId) {
                $li['tax_id'] = $zohoTaxId;
            }
            $lineItems[] = $li;
        }

        $payload = [
            'vendor_id'             => $zohoVendorId,
            'purchaseorder_number'  => $po['purchase_order_number'],
            'date'                  => $po['order_date'],
            'source_of_supply'      => $sourceOfSupply,
            'destination_of_supply' => $companyState,
            'reference_number'      => (string) ($po['reference_number'] ?? ''),
            'line_items'            => $lineItems,
            'notes'                 => (string) ($po['notes'] ?? ''),
            'terms'                 => (string) ($po['terms'] ?? ''),
        ];

        if (!empty($po['delivery_date'])) {
            $payload['delivery_date'] = $po['delivery_date'];
        }

        return $payload;
    }

    public function normalizeLocalPo(array $po, array $items): array
    {
        return [
            'purchase_order_number' => trim((string) ($po['purchase_order_number'] ?? '')),
            'date'                  => (string) ($po['order_date'] ?? ''),
            'subtotal'              => round((float) ($po['subtotal'] ?? 0), 2),
            'tax_amount'            => round((float) ($po['tax_amount'] ?? 0), 2),
            'total_amount'          => round((float) ($po['total_amount'] ?? 0), 2),
            'items_count'           => count($items),
        ];
    }

    public function normalizeZohoPo(array $z): array
    {
        return [
            'purchase_order_number' => trim((string) ($z['purchaseorder_number'] ?? '')),
            'date'                  => (string) ($z['date'] ?? ''),
            'subtotal'              => round((float) ($z['sub_total'] ?? 0), 2),
            'tax_amount'            => round((float) ($z['tax_total'] ?? 0), 2),
            'total_amount'          => round((float) ($z['total'] ?? 0), 2),
            'items_count'           => isset($z['line_items']) && is_array($z['line_items']) ? count($z['line_items']) : 0,
        ];
    }
}

