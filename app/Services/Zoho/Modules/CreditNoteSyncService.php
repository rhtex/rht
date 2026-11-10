<?php

namespace App\Services\Zoho\Modules;

use App\Models\SalesReturnItemModel;
use App\Models\SalesReturnModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class CreditNoteSyncService extends AbstractModuleSyncService
{
    protected SalesReturnModel $returnModel;
    protected SalesReturnItemModel $itemModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->returnModel = new SalesReturnModel();
        $this->itemModel   = new SalesReturnItemModel();
    }

    public function getModuleName(): string
    {
        return 'credit_notes';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('credit_notes', $localId);
        $return  = $this->returnModel->getReturnById($localId);

        if ($operation === 'DELETE' || $operation === 'VOID' || !$return) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Credit Note was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/creditnotes/{$zohoId}", [], [
                'module'          => 'credit_notes',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('credit_notes', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Credit Note deleted in Zoho Books.'];
            }

            $voidRes = $this->client->post("/creditnotes/{$zohoId}/status/void", [], [], [
                'module'          => 'credit_notes',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'VOID_FALLBACK',
            ]);
            if (!empty($voidRes['success'])) {
                $this->mappingModel->markStatus('credit_notes', $localId, 'VOIDED');
                return ['success' => true, 'message' => 'Credit Note voided in Zoho Books.'];
            }

            $this->mappingModel->markStatus('credit_notes', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $items           = $return['items'] ?? [];
        $normalizedLocal = $this->normalizeLocalReturn($return, $items);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Credit Note unchanged since last sync.',
                ];
            }
        }

        $zohoCustomerId = $this->ensureZohoCustomerId((int) $return['customer_id']);
        if (empty($zohoCustomerId)) {
            $msg = 'Unable to sync Customer #' . $return['customer_id'] . ' to Zoho Books for Credit Note.';
            $this->mappingModel->markStatus('credit_notes', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId) && !empty($return['return_number'])) {
            $lookup = $this->client->get('/creditnotes', ['creditnote_number' => $return['return_number']], [
                'module'    => 'credit_notes',
                'operation' => 'LOOKUP_BY_NUMBER',
            ]);
            if (!empty($lookup['data']['creditnotes'][0]['creditnote_id'])) {
                $zohoId = (string) $lookup['data']['creditnotes'][0]['creditnote_id'];
            }
        }

        $payload = $this->buildZohoPayload($return, $items, $zohoCustomerId);
        $query   = ['ignore_auto_number_generation' => 'true'];

        if (!empty($zohoId)) {
            $res = $this->client->put("/creditnotes/{$zohoId}", $payload, $query, [
                'module'          => 'credit_notes',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/creditnotes', $payload, $query, [
                'module'          => 'credit_notes',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('credit_notes', $localId, 'FAILED', $res['message'] ?? 'Credit Note sync failed');
            return $res;
        }

        $zohoCn    = $res['data']['creditnote'] ?? [];
        $newZohoId = (string) ($zohoCn['creditnote_id'] ?? $zohoId);

        // Apply credit note to linked Zoho invoice if invoice_id is set and mapped
        if (!empty($return['invoice_id']) && !empty($newZohoId) && empty($zohoId)) {
            $invMap = $this->mappingModel->findByLocal('invoices', (int) $return['invoice_id']);
            if ($invMap && !empty($invMap['zoho_record_id'])) {
                $this->client->post("/creditnotes/{$newZohoId}/invoices", [
                    'invoices' => [
                        [
                            'invoice_id'     => $invMap['zoho_record_id'],
                            'amount_applied' => round((float) ($return['total_amount'] ?? 0), 2),
                        ],
                    ],
                ], [], [
                    'module'          => 'credit_notes',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $newZohoId,
                    'operation'       => 'APPLY_TO_INVOICE',
                ]);
            }
        }

        $normalizedZoho = !empty($zohoCn) ? $this->normalizeZohoCreditNote($zohoCn) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $return['return_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoCn['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Credit Note synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('credit_notes');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/creditnotes', 'creditnotes', $query, 50, [
            'module'    => 'credit_notes',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_CREDIT_NOTES',
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
            $zohoId = (string) ($summary['creditnote_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('credit_notes', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            $detailRes = $this->client->get("/creditnotes/{$zohoId}", [], [
                'module'         => 'credit_notes',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_CREDIT_NOTE_DETAIL',
            ]);

            $zohoCn   = !empty($detailRes['success']) ? ($detailRes['data']['creditnote'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoCn, !$checkConflicts);
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

            if (!empty($zohoCn['last_modified_time'])) {
                $latestModified = $zohoCn['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('credit_notes', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoCn, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoCn['creditnote_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho creditnote_id'];
        }

        $mapping     = $this->mappingModel->findByZoho('credit_notes', $zohoId);
        $localReturn = null;

        if ($mapping) {
            $localReturn = $this->returnModel->getReturnById((int) $mapping['local_record_id']);
        }
        if (!$localReturn && !empty($zohoCn['creditnote_number'])) {
            $found = $this->returnModel->where('return_number', trim($zohoCn['creditnote_number']))->first();
            if ($found) {
                $localReturn = $this->returnModel->getReturnById((int) $found['id']);
            }
        }

        $normalizedZoho = $this->normalizeZohoCreditNote($zohoCn);

        if ($localReturn && !$forceOverwrite && $mapping) {
            $localId         = (int) $localReturn['id'];
            $normalizedLocal = $this->normalizeLocalReturn($localReturn, $localReturn['items'] ?? []);

            $eval = $this->conflictService->evaluate(
                'credit_notes',
                $localId,
                $zohoId,
                $localReturn['return_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localReturn['updated_at'] ?? null,
                $zohoCn['last_modified_time'] ?? null,
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

        $localCustomerId = $this->ensureLocalCustomerId((string) ($zohoCn['customer_id'] ?? ''), $zohoCn['customer_name'] ?? null);
        if (!$localCustomerId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local customer for Zoho Credit Note ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoCn, $zohoId, $localReturn, $localCustomerId, $normalizedZoho) {
            $companyState  = $this->taxMapper->getCompanyStateCode();
            $placeOfSupply = strtoupper(trim((string) ($zohoCn['place_of_supply'] ?? $companyState)));
            $isInterState  = ($placeOfSupply !== '' && $placeOfSupply !== $companyState) ? 1 : 0;

            $localInvoiceId = $localReturn['invoice_id'] ?? null;
            if (!empty($zohoCn['invoice_id'])) {
                $invMap = $this->mappingModel->findByZoho('invoices', (string) $zohoCn['invoice_id']);
                if ($invMap) {
                    $localInvoiceId = (int) $invMap['local_record_id'];
                }
            } elseif (!empty($zohoCn['invoices_credited'][0]['invoice_id'])) {
                $invMap = $this->mappingModel->findByZoho('invoices', (string) $zohoCn['invoices_credited'][0]['invoice_id']);
                if ($invMap) {
                    $localInvoiceId = (int) $invMap['local_record_id'];
                }
            }

            $returnData = [
                'customer_id'    => $localCustomerId,
                'invoice_id'     => $localInvoiceId,
                'return_number'  => trim((string) ($zohoCn['creditnote_number'] ?? ($localReturn['return_number'] ?? $this->returnModel->generateReturnNumber()))),
                'return_date'    => $zohoCn['date'] ?? date('Y-m-d'),
                'subtotal'       => (float) ($zohoCn['sub_total'] ?? 0),
                'tax_amount'     => (float) ($zohoCn['tax_total'] ?? 0),
                'total_amount'   => (float) ($zohoCn['total'] ?? 0),
                'reason'         => $zohoCn['notes'] ?? ($localReturn['reason'] ?? 'Zoho Books Credit Note'),
                'status'         => 'Open',
                'is_inter_state' => $isInterState,
            ];

            if ($localReturn) {
                $localId = (int) $localReturn['id'];
                $this->returnModel->skipValidation(true)->update($localId, $returnData);
                $this->itemModel->where('sales_return_id', $localId)->delete();
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->returnModel->skipValidation(true)->insert($returnData, true);
                $action = 'CREATED';
            }

            foreach (($zohoCn['line_items'] ?? []) as $li) {
                $prodId   = $this->ensureLocalProductId($li['item_id'] ?? null, $li['name'] ?? null, $li['sku'] ?? null, (float) ($li['rate'] ?? 0), $li['hsn_or_sac'] ?? null);
                $taxRates = $this->taxMapper->extractLineTaxRates($li, (bool) $isInterState);
                $qty      = (float) ($li['quantity'] ?? 1);
                $rate     = (float) ($li['rate'] ?? 0);

                $this->itemModel->insert([
                    'sales_return_id' => $localId,
                    'product_id'      => $prodId ?: 1,
                    'description'     => $li['description'] ?? ($li['name'] ?? ''),
                    'quantity'        => $qty,
                    'rate'            => $rate,
                    'tax_percentage'  => $taxRates['tax_percentage'],
                    'amount'          => (float) ($li['item_total'] ?? ($qty * $rate)),
                ]);
            }

            $updated         = $this->returnModel->getReturnById($localId);
            $normalizedLocal = $this->normalizeLocalReturn($updated, $updated['items'] ?? []);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['return_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoCn['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $return, array $items, string $zohoCustomerId): array
    {
        $isInterState  = !empty($return['is_inter_state']);
        $placeOfSupply = $this->resolveCustomerPlaceOfSupply((int) $return['customer_id'], $isInterState);

        $lineItems = [];
        foreach ($items as $item) {
            $zohoItemId = $this->ensureZohoItemId(!empty($item['product_id']) ? (int) $item['product_id'] : null);
            $taxPct     = (float) ($item['tax_percentage'] ?? 0);
            $zohoTaxId  = $this->taxMapper->resolveZohoTaxId($taxPct, $isInterState);

            $li = [
                'description' => (string) ($item['description'] ?? ($item['product_name'] ?? 'Return Item')),
                'quantity'    => (float) ($item['quantity'] ?? 1),
                'rate'        => round((float) ($item['rate'] ?? 0), 2),
            ];
            if ($zohoItemId) {
                $li['item_id'] = $zohoItemId;
            } else {
                $li['name'] = (string) ($item['description'] ?: 'Return Item');
            }
            if ($zohoTaxId) {
                $li['tax_id'] = $zohoTaxId;
            }
            $lineItems[] = $li;
        }

        return [
            'customer_id'       => $zohoCustomerId,
            'creditnote_number' => $return['return_number'],
            'date'              => $return['return_date'],
            'place_of_supply'   => $placeOfSupply,
            'line_items'        => $lineItems,
            'notes'             => (string) ($return['reason'] ?? ''),
        ];
    }

    public function normalizeLocalReturn(array $r, array $items): array
    {
        return [
            'return_number' => trim((string) ($r['return_number'] ?? '')),
            'date'          => (string) ($r['return_date'] ?? ''),
            'subtotal'      => round((float) ($r['subtotal'] ?? 0), 2),
            'tax_amount'    => round((float) ($r['tax_amount'] ?? 0), 2),
            'total_amount'  => round((float) ($r['total_amount'] ?? 0), 2),
            'items_count'   => count($items),
        ];
    }

    public function normalizeZohoCreditNote(array $z): array
    {
        return [
            'return_number' => trim((string) ($z['creditnote_number'] ?? '')),
            'date'          => (string) ($z['date'] ?? ''),
            'subtotal'      => round((float) ($z['sub_total'] ?? 0), 2),
            'tax_amount'    => round((float) ($z['tax_total'] ?? 0), 2),
            'total_amount'  => round((float) ($z['total'] ?? 0), 2),
            'items_count'   => isset($z['line_items']) && is_array($z['line_items']) ? count($z['line_items']) : 0,
        ];
    }
}

