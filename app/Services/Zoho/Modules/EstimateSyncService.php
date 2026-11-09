<?php

namespace App\Services\Zoho\Modules;

use App\Models\QuotationItemModel;
use App\Models\QuotationModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class EstimateSyncService extends AbstractModuleSyncService
{
    protected QuotationModel $quotationModel;
    protected QuotationItemModel $itemModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->quotationModel = new QuotationModel();
        $this->itemModel      = new QuotationItemModel();
    }

    public function getModuleName(): string
    {
        return 'quotations';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping   = $this->mappingModel->findByLocal('quotations', $localId);
        $quotation = $this->quotationModel->getQuotationById($localId);

        if ($operation === 'DELETE' || !$quotation) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Quotation was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/estimates/{$zohoId}", [], [
                'module'          => 'quotations',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('quotations', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Estimate deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('quotations', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $items           = $quotation['items'] ?? [];
        $normalizedLocal = $this->normalizeLocalQuotation($quotation, $items);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Quotation unchanged since last sync.',
                ];
            }
        }

        $zohoCustomerId = $this->ensureZohoCustomerId((int) $quotation['customer_id']);
        if (empty($zohoCustomerId)) {
            $msg = 'Unable to sync Customer #' . $quotation['customer_id'] . ' to Zoho Books for Quotation.';
            $this->mappingModel->markStatus('quotations', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId) && !empty($quotation['quotation_number'])) {
            $lookup = $this->client->get('/estimates', ['estimate_number' => $quotation['quotation_number']], [
                'module'    => 'quotations',
                'operation' => 'LOOKUP_BY_NUMBER',
            ]);
            if (!empty($lookup['data']['estimates'][0]['estimate_id'])) {
                $zohoId = (string) $lookup['data']['estimates'][0]['estimate_id'];
            }
        }

        $payload = $this->buildZohoPayload($quotation, $items, $zohoCustomerId);
        $query   = ['ignore_auto_number_generation' => 'true'];

        if (!empty($zohoId)) {
            $res = $this->client->put("/estimates/{$zohoId}", $payload, $query, [
                'module'          => 'quotations',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/estimates', $payload, $query, [
                'module'          => 'quotations',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('quotations', $localId, 'FAILED', $res['message'] ?? 'Estimate sync failed');
            return $res;
        }

        $zohoEst        = $res['data']['estimate'] ?? [];
        $newZohoId      = (string) ($zohoEst['estimate_id'] ?? $zohoId);
        $normalizedZoho = !empty($zohoEst) ? $this->normalizeZohoEstimate($zohoEst) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $quotation['quotation_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoEst['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Quotation synchronized to Zoho Books Estimate.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('quotations');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/estimates', 'estimates', $query, 50, [
            'module'    => 'quotations',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_ESTIMATES',
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
            $zohoId = (string) ($summary['estimate_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('quotations', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            $detailRes = $this->client->get("/estimates/{$zohoId}", [], [
                'module'         => 'quotations',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_ESTIMATE_DETAIL',
            ]);

            $zohoEst  = !empty($detailRes['success']) ? ($detailRes['data']['estimate'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoEst, !$checkConflicts);
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

            if (!empty($zohoEst['last_modified_time'])) {
                $latestModified = $zohoEst['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('quotations', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoEst, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoEst['estimate_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho estimate_id'];
        }

        $mapping   = $this->mappingModel->findByZoho('quotations', $zohoId);
        $localQuot = null;

        if ($mapping) {
            $localQuot = $this->quotationModel->getQuotationById((int) $mapping['local_record_id']);
        }
        if (!$localQuot && !empty($zohoEst['estimate_number'])) {
            $found = $this->quotationModel->where('quotation_number', trim($zohoEst['estimate_number']))->first();
            if ($found) {
                $localQuot = $this->quotationModel->getQuotationById((int) $found['id']);
            }
        }

        $normalizedZoho = $this->normalizeZohoEstimate($zohoEst);

        if ($localQuot && !$forceOverwrite && $mapping) {
            $localId         = (int) $localQuot['id'];
            $normalizedLocal = $this->normalizeLocalQuotation($localQuot, $localQuot['items'] ?? []);

            $eval = $this->conflictService->evaluate(
                'quotations',
                $localId,
                $zohoId,
                $localQuot['quotation_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localQuot['updated_at'] ?? null,
                $zohoEst['last_modified_time'] ?? null,
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

        $localCustomerId = $this->ensureLocalCustomerId((string) ($zohoEst['customer_id'] ?? ''), $zohoEst['customer_name'] ?? null);
        if (!$localCustomerId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local customer for Zoho Estimate ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoEst, $zohoId, $localQuot, $localCustomerId, $normalizedZoho) {
            $companyState = $this->taxMapper->getCompanyStateCode();
            $placeOfSupply = strtoupper(trim((string) ($zohoEst['place_of_supply'] ?? $companyState)));
            $isInterState  = ($placeOfSupply !== '' && $placeOfSupply !== $companyState) ? 1 : 0;

            $statusMap = [
                'draft'    => 'Draft',
                'sent'     => 'Sent',
                'accepted' => 'Accepted',
                'declined' => 'Declined',
                'invoiced' => 'Invoiced',
                'expired'  => 'Expired',
            ];
            $zohoStatus = strtolower((string) ($zohoEst['status'] ?? 'draft'));

            $quotData = [
                'customer_id'      => $localCustomerId,
                'quotation_number' => trim((string) ($zohoEst['estimate_number'] ?? ($localQuot['quotation_number'] ?? $this->quotationModel->generateQuotationNumber()))),
                'quotation_date'   => $zohoEst['date'] ?? date('Y-m-d'),
                'expiry_date'      => $zohoEst['expiry_date'] ?? null,
                'reference_number' => $zohoEst['reference_number'] ?? null,
                'status'           => $statusMap[$zohoStatus] ?? 'Draft',
                'is_inter_state'   => $isInterState,
                'subtotal'         => (float) ($zohoEst['sub_total'] ?? 0),
                'discount_amount'  => (float) ($zohoEst['discount_total'] ?? $zohoEst['discount'] ?? 0),
                'discount_type'    => 'Fixed',
                'shipping_charge'  => (float) ($zohoEst['shipping_charge'] ?? 0),
                'tax_amount'       => (float) ($zohoEst['tax_total'] ?? 0),
                'roundoff_amount'  => (float) ($zohoEst['adjustment'] ?? 0),
                'total_amount'     => (float) ($zohoEst['total'] ?? 0),
                'notes'            => $zohoEst['notes'] ?? null,
                'terms'            => $zohoEst['terms'] ?? null,
            ];

            if ($localQuot) {
                $localId = (int) $localQuot['id'];
                $this->quotationModel->skipValidation(true)->update($localId, $quotData);
                $this->itemModel->where('quotation_id', $localId)->delete();
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->quotationModel->skipValidation(true)->insert($quotData, true);
                $action = 'CREATED';
            }

            foreach (($zohoEst['line_items'] ?? []) as $li) {
                $prodId   = $this->ensureLocalProductId($li['item_id'] ?? null, $li['name'] ?? null, $li['sku'] ?? null, (float) ($li['rate'] ?? 0), $li['hsn_or_sac'] ?? null);
                $taxRates = $this->taxMapper->extractLineTaxRates($li, (bool) $isInterState);
                $qty      = (float) ($li['quantity'] ?? 1);
                $rate     = (float) ($li['rate'] ?? 0);

                $this->itemModel->insert([
                    'quotation_id'   => $localId,
                    'product_id'     => $prodId,
                    'description'    => $li['description'] ?? ($li['name'] ?? ''),
                    'hsn_code'       => $li['hsn_or_sac'] ?? null,
                    'quantity'       => $qty,
                    'rate'           => $rate,
                    'tax_percentage' => $taxRates['tax_percentage'],
                    'amount'         => (float) ($li['item_total'] ?? ($qty * $rate)),
                ]);
            }

            $updated         = $this->quotationModel->getQuotationById($localId);
            $normalizedLocal = $this->normalizeLocalQuotation($updated, $updated['items'] ?? []);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['quotation_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoEst['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $quotation, array $items, string $zohoCustomerId): array
    {
        $isInterState  = !empty($quotation['is_inter_state']);
        $placeOfSupply = $this->resolveCustomerPlaceOfSupply((int) $quotation['customer_id'], $isInterState);

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

        $discountVal = (float) ($quotation['discount_amount'] ?? 0);
        $discountStr = ($quotation['discount_type'] ?? 'Fixed') === 'Percentage'
            ? $discountVal . '%'
            : round($discountVal, 2);

        $payload = [
            'customer_id'             => $zohoCustomerId,
            'estimate_number'         => $quotation['quotation_number'],
            'date'                    => $quotation['quotation_date'],
            'place_of_supply'         => $placeOfSupply,
            'reference_number'        => (string) ($quotation['reference_number'] ?? ''),
            'line_items'              => $lineItems,
            'discount'                => $discountStr,
            'is_discount_before_tax'  => true,
            'discount_type'           => 'entity_level',
            'shipping_charge'         => round((float) ($quotation['shipping_charge'] ?? 0), 2),
            'adjustment'              => round((float) ($quotation['roundoff_amount'] ?? 0), 2),
            'adjustment_description'  => 'Round Off',
            'notes'                   => (string) ($quotation['notes'] ?? ''),
            'terms'                   => (string) ($quotation['terms'] ?? ''),
        ];

        if (!empty($quotation['expiry_date'])) {
            $payload['expiry_date'] = $quotation['expiry_date'];
        }

        return $payload;
    }

    public function normalizeLocalQuotation(array $q, array $items): array
    {
        return [
            'quotation_number' => trim((string) ($q['quotation_number'] ?? '')),
            'date'             => (string) ($q['quotation_date'] ?? ''),
            'subtotal'         => round((float) ($q['subtotal'] ?? 0), 2),
            'discount_amount'  => round((float) ($q['discount_amount'] ?? 0), 2),
            'tax_amount'       => round((float) ($q['tax_amount'] ?? 0), 2),
            'total_amount'     => round((float) ($q['total_amount'] ?? 0), 2),
            'items_count'      => count($items),
        ];
    }

    public function normalizeZohoEstimate(array $z): array
    {
        return [
            'quotation_number' => trim((string) ($z['estimate_number'] ?? '')),
            'date'             => (string) ($z['date'] ?? ''),
            'subtotal'         => round((float) ($z['sub_total'] ?? 0), 2),
            'discount_amount'  => round((float) ($z['discount_total'] ?? $z['discount'] ?? 0), 2),
            'tax_amount'       => round((float) ($z['tax_total'] ?? 0), 2),
            'total_amount'     => round((float) ($z['total'] ?? 0), 2),
            'items_count'      => isset($z['line_items']) && is_array($z['line_items']) ? count($z['line_items']) : 0,
        ];
    }
}

