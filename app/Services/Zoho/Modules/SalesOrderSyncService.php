<?php

namespace App\Services\Zoho\Modules;

use App\Models\SalesOrderItemModel;
use App\Models\SalesOrderModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class SalesOrderSyncService extends AbstractModuleSyncService
{
    protected SalesOrderModel $orderModel;
    protected SalesOrderItemModel $itemModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->orderModel = new SalesOrderModel();
        $this->itemModel  = new SalesOrderItemModel();
    }

    public function getModuleName(): string
    {
        return 'sales_orders';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('sales_orders', $localId);
        $order   = $this->orderModel->getOrderById($localId);

        if ($operation === 'DELETE' || $operation === 'VOID' || !$order) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Sales Order was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            if ($operation === 'VOID' || ($order && ($order['status'] ?? '') === 'Void')) {
                $voidRes = $this->client->post("/salesorders/{$zohoId}/status/void", [], [], [
                    'module'          => 'sales_orders',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $zohoId,
                    'operation'       => 'VOID',
                ]);
                if (!empty($voidRes['success'])) {
                    $this->mappingModel->markStatus('sales_orders', $localId, 'VOIDED');
                    return ['success' => true, 'message' => 'Sales Order marked Void in Zoho Books.'];
                }
            }

            $delRes = $this->client->delete("/salesorders/{$zohoId}", [], [
                'module'          => 'sales_orders',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('sales_orders', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Sales Order deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('sales_orders', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $items           = $order['items'] ?? [];
        $normalizedLocal = $this->normalizeLocalOrder($order, $items);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Sales Order unchanged since last sync.',
                ];
            }
        }

        $zohoCustomerId = $this->ensureZohoCustomerId((int) $order['customer_id']);
        if (empty($zohoCustomerId)) {
            $msg = 'Unable to sync Customer #' . $order['customer_id'] . ' to Zoho Books for Sales Order.';
            $this->mappingModel->markStatus('sales_orders', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId) && !empty($order['sales_order_number'])) {
            $lookup = $this->client->get('/salesorders', ['salesorder_number' => $order['sales_order_number']], [
                'module'    => 'sales_orders',
                'operation' => 'LOOKUP_BY_NUMBER',
            ]);
            if (!empty($lookup['data']['salesorders'][0]['salesorder_id'])) {
                $zohoId = (string) $lookup['data']['salesorders'][0]['salesorder_id'];
            }
        }

        $payload = $this->buildZohoPayload($order, $items, $zohoCustomerId);
        $query   = ['ignore_auto_number_generation' => 'true'];

        if (!empty($zohoId)) {
            $res = $this->client->put("/salesorders/{$zohoId}", $payload, $query, [
                'module'          => 'sales_orders',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/salesorders', $payload, $query, [
                'module'          => 'sales_orders',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('sales_orders', $localId, 'FAILED', $res['message'] ?? 'Sales Order sync failed');
            return $res;
        }

        $zohoSo         = $res['data']['salesorder'] ?? [];
        $newZohoId      = (string) ($zohoSo['salesorder_id'] ?? $zohoId);
        $normalizedZoho = !empty($zohoSo) ? $this->normalizeZohoOrder($zohoSo) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $order['sales_order_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoSo['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Sales Order synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('sales_orders');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/salesorders', 'salesorders', $query, 50, [
            'module'    => 'sales_orders',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_SALES_ORDERS',
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
            $zohoId = (string) ($summary['salesorder_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('sales_orders', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            $detailRes = $this->client->get("/salesorders/{$zohoId}", [], [
                'module'         => 'sales_orders',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_SALES_ORDER_DETAIL',
            ]);

            $zohoSo   = !empty($detailRes['success']) ? ($detailRes['data']['salesorder'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoSo, !$checkConflicts);
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

            if (!empty($zohoSo['last_modified_time'])) {
                $latestModified = $zohoSo['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('sales_orders', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoSo, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoSo['salesorder_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho salesorder_id'];
        }

        $mapping    = $this->mappingModel->findByZoho('sales_orders', $zohoId);
        $localOrder = null;

        if ($mapping) {
            $localOrder = $this->orderModel->getOrderById((int) $mapping['local_record_id']);
        }
        if (!$localOrder && !empty($zohoSo['salesorder_number'])) {
            $found = $this->orderModel->where('sales_order_number', trim($zohoSo['salesorder_number']))->first();
            if ($found) {
                $localOrder = $this->orderModel->getOrderById((int) $found['id']);
            }
        }

        $normalizedZoho = $this->normalizeZohoOrder($zohoSo);

        if ($localOrder && !$forceOverwrite && $mapping) {
            $localId         = (int) $localOrder['id'];
            $normalizedLocal = $this->normalizeLocalOrder($localOrder, $localOrder['items'] ?? []);

            $eval = $this->conflictService->evaluate(
                'sales_orders',
                $localId,
                $zohoId,
                $localOrder['sales_order_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localOrder['updated_at'] ?? null,
                $zohoSo['last_modified_time'] ?? null,
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

        $localCustomerId = $this->ensureLocalCustomerId((string) ($zohoSo['customer_id'] ?? ''), $zohoSo['customer_name'] ?? null);
        if (!$localCustomerId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local customer for Zoho Sales Order ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoSo, $zohoId, $localOrder, $localCustomerId, $normalizedZoho) {
            $companyState  = $this->taxMapper->getCompanyStateCode();
            $placeOfSupply = strtoupper(trim((string) ($zohoSo['place_of_supply'] ?? $companyState)));
            $isInterState  = ($placeOfSupply !== '' && $placeOfSupply !== $companyState) ? 1 : 0;

            $statusMap = [
                'draft'     => 'Draft',
                'open'      => 'Confirmed',
                'confirmed' => 'Confirmed',
                'invoiced'  => 'Closed',
                'closed'    => 'Closed',
                'void'      => 'Void',
            ];
            $zohoStatus = strtolower((string) ($zohoSo['status'] ?? 'draft'));

            $orderData = [
                'customer_id'        => $localCustomerId,
                'sales_order_number' => trim((string) ($zohoSo['salesorder_number'] ?? ($localOrder['sales_order_number'] ?? $this->orderModel->generateOrderNumber()))),
                'order_date'         => $zohoSo['date'] ?? date('Y-m-d'),
                'shipment_date'      => $zohoSo['shipment_date'] ?? null,
                'reference_number'   => $zohoSo['reference_number'] ?? null,
                'transport_name'     => $zohoSo['delivery_method'] ?? ($localOrder['transport_name'] ?? null),
                'status'             => $statusMap[$zohoStatus] ?? 'Confirmed',
                'is_inter_state'     => $isInterState,
                'subtotal'           => (float) ($zohoSo['sub_total'] ?? 0),
                'discount_amount'    => (float) ($zohoSo['discount_total'] ?? $zohoSo['discount'] ?? 0),
                'discount_type'      => 'Fixed',
                'shipping_charge'    => (float) ($zohoSo['shipping_charge'] ?? 0),
                'roundoff_amount'    => (float) ($zohoSo['adjustment'] ?? 0),
                'tax_amount'         => (float) ($zohoSo['tax_total'] ?? 0),
                'total_amount'       => (float) ($zohoSo['total'] ?? 0),
                'notes'              => $zohoSo['notes'] ?? null,
                'terms'              => $zohoSo['terms'] ?? null,
            ];

            if ($localOrder) {
                $localId = (int) $localOrder['id'];
                $this->orderModel->skipValidation(true)->update($localId, $orderData);
                $this->itemModel->where('sales_order_id', $localId)->delete();
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->orderModel->skipValidation(true)->insert($orderData, true);
                $action = 'CREATED';
            }

            foreach (($zohoSo['line_items'] ?? []) as $li) {
                $prodId   = $this->ensureLocalProductId($li['item_id'] ?? null, $li['name'] ?? null, $li['sku'] ?? null, (float) ($li['rate'] ?? 0), $li['hsn_or_sac'] ?? null);
                $taxRates = $this->taxMapper->extractLineTaxRates($li, (bool) $isInterState);
                $qty      = (float) ($li['quantity'] ?? 1);
                $rate     = (float) ($li['rate'] ?? 0);

                $this->itemModel->insert([
                    'sales_order_id' => $localId,
                    'product_id'     => $prodId,
                    'description'    => $li['description'] ?? ($li['name'] ?? ''),
                    'hsn_code'       => $li['hsn_or_sac'] ?? null,
                    'quantity'       => $qty,
                    'rate'           => $rate,
                    'tax_percentage' => $taxRates['tax_percentage'],
                    'amount'         => (float) ($li['item_total'] ?? ($qty * $rate)),
                ]);
            }

            $updated         = $this->orderModel->getOrderById($localId);
            $normalizedLocal = $this->normalizeLocalOrder($updated, $updated['items'] ?? []);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['sales_order_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoSo['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $order, array $items, string $zohoCustomerId): array
    {
        $isInterState  = !empty($order['is_inter_state']);
        $placeOfSupply = $this->resolveCustomerPlaceOfSupply((int) $order['customer_id'], $isInterState);

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
                $li['name'] = (string) ($item['description'] ?: 'Item');
            }
            if (!empty($item['hsn_code'])) {
                $li['hsn_or_sac'] = trim((string) $item['hsn_code']);
            }
            if ($zohoTaxId) {
                $li['tax_id'] = $zohoTaxId;
            }
            $lineItems[] = $li;
        }

        $discountVal = (float) ($order['discount_amount'] ?? 0);
        $discountStr = ($order['discount_type'] ?? 'Fixed') === 'Percentage'
            ? $discountVal . '%'
            : round($discountVal, 2);

        $payload = [
            'customer_id'             => $zohoCustomerId,
            'salesorder_number'       => $order['sales_order_number'],
            'date'                    => $order['order_date'],
            'place_of_supply'         => $placeOfSupply,
            'reference_number'        => (string) ($order['reference_number'] ?? ''),
            'delivery_method'         => (string) ($order['transport_name'] ?? ''),
            'line_items'              => $lineItems,
            'discount'                => $discountStr,
            'is_discount_before_tax'  => true,
            'discount_type'           => 'entity_level',
            'shipping_charge'         => round((float) ($order['shipping_charge'] ?? 0), 2),
            'adjustment'              => round((float) ($order['roundoff_amount'] ?? 0), 2),
            'adjustment_description'  => 'Round Off',
            'notes'                   => (string) ($order['notes'] ?? ''),
            'terms'                   => (string) ($order['terms'] ?? ''),
        ];

        if (!empty($order['shipment_date'])) {
            $payload['shipment_date'] = $order['shipment_date'];
        }

        return $payload;
    }

    public function normalizeLocalOrder(array $o, array $items): array
    {
        return [
            'sales_order_number' => trim((string) ($o['sales_order_number'] ?? '')),
            'date'               => (string) ($o['order_date'] ?? ''),
            'subtotal'           => round((float) ($o['subtotal'] ?? 0), 2),
            'discount_amount'    => round((float) ($o['discount_amount'] ?? 0), 2),
            'tax_amount'         => round((float) ($o['tax_amount'] ?? 0), 2),
            'total_amount'       => round((float) ($o['total_amount'] ?? 0), 2),
            'status'             => trim((string) ($o['status'] ?? 'Draft')),
            'items_count'        => count($items),
        ];
    }

    public function normalizeZohoOrder(array $z): array
    {
        $statusMap = [
            'draft'     => 'Draft',
            'open'      => 'Confirmed',
            'confirmed' => 'Confirmed',
            'invoiced'  => 'Closed',
            'closed'    => 'Closed',
            'void'      => 'Void',
        ];
        $zStatus = strtolower((string) ($z['status'] ?? 'draft'));
        return [
            'sales_order_number' => trim((string) ($z['salesorder_number'] ?? '')),
            'date'               => (string) ($z['date'] ?? ''),
            'subtotal'           => round((float) ($z['sub_total'] ?? 0), 2),
            'discount_amount'    => round((float) ($z['discount_total'] ?? $z['discount'] ?? 0), 2),
            'tax_amount'         => round((float) ($z['tax_total'] ?? 0), 2),
            'total_amount'       => round((float) ($z['total'] ?? 0), 2),
            'status'             => $statusMap[$zStatus] ?? 'Confirmed',
            'items_count'        => isset($z['line_items']) && is_array($z['line_items']) ? count($z['line_items']) : 0,
        ];
    }
}

