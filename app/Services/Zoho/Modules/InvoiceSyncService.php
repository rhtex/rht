<?php

namespace App\Services\Zoho\Modules;

use App\Models\AccountingModel;
use App\Models\InvoiceItemModel;
use App\Models\InvoiceModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class InvoiceSyncService extends AbstractModuleSyncService
{
    protected InvoiceModel $invoiceModel;
    protected InvoiceItemModel $itemModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->invoiceModel = new InvoiceModel();
        $this->itemModel    = new InvoiceItemModel();
    }

    public function getModuleName(): string
    {
        return 'invoices';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('invoices', $localId);
        $invoice = $this->invoiceModel->getInvoiceById($localId);

        // Handle Delete or Void
        if ($operation === 'DELETE' || $operation === 'VOID' || !$invoice || ($invoice['status'] ?? '') === 'Void') {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Invoice was not synced to Zoho; nothing to delete/void.'];
            }
            $zohoId = $mapping['zoho_record_id'];

            if ($invoice && (float) ($invoice['paid_amount'] ?? 0) > 0) {
                $msg = 'Cannot delete or void Invoice #' . $invoice['invoice_number'] . ' in Zoho Books because payments are applied. Please delete or unlink the receipt first.';
                $this->mappingModel->markStatus('invoices', $localId, 'FAILED', $msg);
                return ['success' => false, 'message' => $msg];
            }

            if ($operation === 'VOID' || ($invoice && ($invoice['status'] ?? '') === 'Void')) {
                $voidRes = $this->client->post("/invoices/{$zohoId}/status/void", [], [], [
                    'module'          => 'invoices',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $zohoId,
                    'operation'       => 'VOID',
                ]);
                if (!empty($voidRes['success'])) {
                    $this->mappingModel->markStatus('invoices', $localId, 'VOIDED');
                    return ['success' => true, 'message' => 'Invoice voided in Zoho Books.'];
                }
                $this->mappingModel->markStatus('invoices', $localId, 'FAILED', $voidRes['message'] ?? 'Void failed in Zoho Books');
                return $voidRes;
            }

            $delRes = $this->client->delete("/invoices/{$zohoId}", [], [
                'module'          => 'invoices',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('invoices', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Invoice deleted in Zoho Books.'];
            }

            // Fallback to Void if Zoho restricts hard-deleting an issued invoice
            $voidFallback = $this->client->post("/invoices/{$zohoId}/status/void", [], [], [
                'module'          => 'invoices',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'VOID_FALLBACK',
            ]);
            if (!empty($voidFallback['success'])) {
                $this->mappingModel->markStatus('invoices', $localId, 'VOIDED');
                return ['success' => true, 'message' => 'Invoice voided in Zoho Books (hard delete restricted).'];
            }

            $this->mappingModel->markStatus('invoices', $localId, 'FAILED', $delRes['message'] ?? 'Delete restricted in Zoho Books');
            return $delRes;
        }

        $items           = $invoice['items'] ?? [];
        $normalizedLocal = $this->normalizeLocalInvoice($invoice, $items);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Invoice unchanged since last sync.',
                ];
            }
        }

        $zohoCustomerId = $this->ensureZohoCustomerId((int) $invoice['customer_id']);
        if (empty($zohoCustomerId)) {
            $msg = 'Unable to sync Customer #' . $invoice['customer_id'] . ' to Zoho Books for Invoice #' . $invoice['invoice_number'];
            $this->mappingModel->markStatus('invoices', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId) && !empty($invoice['invoice_number'])) {
            $lookup = $this->client->get('/invoices', ['invoice_number' => $invoice['invoice_number']], [
                'module'    => 'invoices',
                'operation' => 'LOOKUP_BY_NUMBER',
            ]);
            if (!empty($lookup['data']['invoices'][0]['invoice_id'])) {
                $zohoId = (string) $lookup['data']['invoices'][0]['invoice_id'];
            }
        }

        $payload = $this->buildZohoPayload($invoice, $items, $zohoCustomerId);
        $query   = ['ignore_auto_number_generation' => 'true'];

        if (!empty($zohoId)) {
            // When updating a sent/existing invoice, Zoho Books requires a reason string in the payload
            if (empty($payload['reason'])) {
                $reqReason = trim((string) (service('request')->getPost('zoho_update_reason') ?? ''));
                $payload['reason'] = $reqReason !== '' ? $reqReason : 'Updated invoice details from ERP';
            }
            $res = $this->client->put("/invoices/{$zohoId}", $payload, $query, [
                'module'          => 'invoices',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/invoices', $payload, $query, [
                'module'          => 'invoices',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('invoices', $localId, 'FAILED', $res['message'] ?? 'Invoice sync failed');
            return $res;
        }

        $zohoInv   = $res['data']['invoice'] ?? [];
        $newZohoId = (string) ($zohoInv['invoice_id'] ?? $zohoId);

        // If local invoice is Open/Paid/Partially Paid and Zoho created it as Draft, mark as Sent in Zoho so it posts to Zoho AR
        $localStatus = $invoice['status'] ?? 'Open';
        $zohoStatus  = strtolower((string) ($zohoInv['status'] ?? 'draft'));
        if ($localStatus !== 'Draft' && $zohoStatus === 'draft' && !empty($newZohoId)) {
            $this->client->post("/invoices/{$newZohoId}/status/sent", [], [], [
                'module'          => 'invoices',
                'local_record_id' => $localId,
                'zoho_record_id'  => $newZohoId,
                'operation'       => 'MARK_SENT',
            ]);
            $zohoInv['status'] = 'sent';
        }

        $normalizedZoho = !empty($zohoInv) ? $this->normalizeZohoInvoice($zohoInv) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $invoice['invoice_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoInv['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Invoice synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('invoices');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/invoices', 'invoices', $query, 50, [
            'module'    => 'invoices',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_INVOICES',
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
        $this->emitProgress(15, "Retrieved {$totalRecords} invoice(s) from Zoho Books. Starting import...", ['total' => $totalRecords]);

        foreach ($listRes['records'] as $summary) {
            $idx++;
            $stats['total_processed']++;
            $zohoId = (string) ($summary['invoice_id'] ?? '');
            $invNum = $summary['invoice_number'] ?? $zohoId;
            $pct    = 15 + (int) round(($idx / max(1, $totalRecords)) * 80);

            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('invoices', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Skipped unchanged Invoice #{$invNum}", ['processed' => $idx, 'total' => $totalRecords]);
                continue;
            }

            $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Importing Invoice #{$invNum}...", ['processed' => $idx, 'total' => $totalRecords]);

            $detailRes = $this->client->get("/invoices/{$zohoId}", [], [
                'module'         => 'invoices',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_INVOICE_DETAIL',
            ]);

            $zohoInv  = !empty($detailRes['success']) ? ($detailRes['data']['invoice'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoInv, !$checkConflicts);
            $act      = $applyRes['action'] ?? 'SKIPPED';
            if ($act === 'CREATED') {
                $stats['created_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Created Invoice #{$invNum}", ['action' => 'CREATED']);
            } elseif ($act === 'UPDATED') {
                $stats['updated_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Updated Invoice #{$invNum}", ['action' => 'UPDATED']);
            } elseif ($act === 'CONFLICT') {
                $stats['conflict_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Conflict on Invoice #{$invNum}", ['action' => 'CONFLICT']);
            } elseif ($act === 'FAILED') {
                $stats['failed_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Failed Invoice #{$invNum}: " . ($applyRes['message'] ?? 'Error'), ['action' => 'FAILED']);
            } else {
                $stats['skipped_count']++;
            }

            if (!empty($zohoInv['last_modified_time'])) {
                $latestModified = $zohoInv['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('invoices', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoInv, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoInv['invoice_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho invoice_id'];
        }

        $mapping  = $this->mappingModel->findByZoho('invoices', $zohoId);
        $localInv = null;

        if ($mapping) {
            $localInv = $this->invoiceModel->getInvoiceById((int) $mapping['local_record_id']);
        }
        if (!$localInv && !empty($zohoInv['invoice_number'])) {
            $found = $this->invoiceModel->where('invoice_number', trim($zohoInv['invoice_number']))->first();
            if ($found) {
                $localInv = $this->invoiceModel->getInvoiceById((int) $found['id']);
            }
        }

        $normalizedZoho = $this->normalizeZohoInvoice($zohoInv);

        if ($localInv && !$forceOverwrite && $mapping) {
            $localId         = (int) $localInv['id'];
            $normalizedLocal = $this->normalizeLocalInvoice($localInv, $localInv['items'] ?? []);

            $eval = $this->conflictService->evaluate(
                'invoices',
                $localId,
                $zohoId,
                $localInv['invoice_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localInv['updated_at'] ?? null,
                $zohoInv['last_modified_time'] ?? null,
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

        $localCustomerId = $this->ensureLocalCustomerId((string) ($zohoInv['customer_id'] ?? ''), $zohoInv['customer_name'] ?? null);
        if (!$localCustomerId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local customer for Zoho Invoice ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoInv, $zohoId, $localInv, $localCustomerId, $normalizedZoho) {
            $companyState  = $this->taxMapper->getCompanyStateCode();
            $placeOfSupply = strtoupper(trim((string) ($zohoInv['place_of_supply'] ?? $companyState)));
            $isInterState  = ($placeOfSupply !== '' && $placeOfSupply !== $companyState) ? 1 : 0;

            $taxTotal  = (float) ($zohoInv['tax_total'] ?? 0);
            $cgstTotal = $isInterState ? 0.00 : round($taxTotal / 2, 2);
            $sgstTotal = $isInterState ? 0.00 : round($taxTotal - $cgstTotal, 2);
            $igstTotal = $isInterState ? round($taxTotal, 2) : 0.00;

            $total   = (float) ($zohoInv['total'] ?? 0);
            $balance = (float) ($zohoInv['balance'] ?? 0);
            $paid    = max(0.00, round($total - $balance, 2));

            $zohoStatus = strtolower((string) ($zohoInv['status'] ?? 'sent'));
            $statusMap  = [
                'draft'          => 'Draft',
                'sent'           => 'Open',
                'viewed'         => 'Open',
                'unpaid'         => 'Open',
                'overdue'        => 'Overdue',
                'paid'           => 'Paid',
                'partially_paid' => 'Partially Paid',
                'void'           => 'Void',
            ];
            $localStatus = $statusMap[$zohoStatus] ?? 'Open';

            // Extract custom fields from Zoho if present (e.g. cf_no_of_bales, cf_transport)
            $balesCount = null;
            if (isset($zohoInv['custom_field_hash']['cf_no_of_bales'])) {
                $balesCount = (int) $zohoInv['custom_field_hash']['cf_no_of_bales'];
            } elseif (!empty($zohoInv['custom_fields']) && is_array($zohoInv['custom_fields'])) {
                foreach ($zohoInv['custom_fields'] as $cf) {
                    if (($cf['api_name'] ?? '') === 'cf_no_of_bales' || stripos($cf['label'] ?? '', 'bale') !== false) {
                        $balesCount = (int) ($cf['value'] ?? null);
                        break;
                    }
                }
            }

            // Extract e-Way bill details from Zoho
            $zohoEwaybillNumber = !empty($zohoInv['ewaybill_number']) ? trim((string) $zohoInv['ewaybill_number']) : null;
            $zohoEwbDetails     = !empty($zohoInv['eway_bill_details']) && is_array($zohoInv['eway_bill_details']) ? $zohoInv['eway_bill_details'] : [];
            $zohoEwbList        = !empty($zohoInv['ewaybills']) && is_array($zohoInv['ewaybills']) ? $zohoInv['ewaybills'] : [];

            $ewbNumber   = $zohoEwaybillNumber ?: ($zohoEwbDetails['ewaybill_number'] ?? null);
            $ewbId       = $zohoEwbDetails['ewaybill_id'] ?? null;
            $ewbExpiry   = $zohoEwbDetails['ewaybill_expiry_date'] ?? ($zohoEwbDetails['valid_until'] ?? null);
            $ewbDate     = $zohoEwbDetails['ewaybill_date'] ?? null;
            $ewbStatus   = $zohoEwbDetails['ewaybill_status'] ?? ($zohoEwbDetails['status'] ?? null);

            if (empty($ewbNumber) && !empty($zohoEwbList)) {
                foreach ($zohoEwbList as $ewbItem) {
                    if (!empty($ewbItem['ewaybill_number'])) {
                        $ewbNumber = trim((string) $ewbItem['ewaybill_number']);
                        $ewbId     = $ewbItem['ewaybill_id'] ?? $ewbId;
                        $ewbExpiry = $ewbItem['ewaybill_expiry_date'] ?? ($ewbItem['valid_until'] ?? $ewbExpiry);
                        $ewbDate   = $ewbItem['ewaybill_date'] ?? $ewbDate;
                        $ewbStatus = $ewbItem['ewaybill_status'] ?? ($ewbItem['status'] ?? $ewbStatus);
                        break;
                    }
                }
            }

            $invData = [
                'customer_id'      => $localCustomerId,
                'invoice_number'   => trim((string) ($zohoInv['invoice_number'] ?? ($localInv['invoice_number'] ?? $this->invoiceModel->generateInvoiceNumber()))),
                'invoice_date'     => $zohoInv['date'] ?? date('Y-m-d'),
                'due_date'         => $zohoInv['due_date'] ?? ($zohoInv['date'] ?? date('Y-m-d')),
                'reference_number' => $zohoInv['reference_number'] ?? null,
                'ewaybill_number'  => $ewbNumber ?? ($localInv['ewaybill_number'] ?? null),
                'packages_count'   => $balesCount ?? ($localInv['packages_count'] ?? ($localInv['bales_count'] ?? null)),
                'bales_count'      => $balesCount ?? ($localInv['bales_count'] ?? null),
                'status'           => $localStatus,
                'is_inter_state'   => $isInterState,
                'subtotal'         => (float) ($zohoInv['sub_total'] ?? 0),
                'discount_amount'  => (float) ($zohoInv['discount_total'] ?? $zohoInv['discount'] ?? 0),
                'discount_type'    => 'Fixed',
                'shipping_charge'  => (float) ($zohoInv['shipping_charge'] ?? 0),
                'cgst_amount'      => $cgstTotal,
                'sgst_amount'      => $sgstTotal,
                'igst_amount'      => $igstTotal,
                'tax_amount'       => $taxTotal,
                'roundoff_amount'  => (float) ($zohoInv['adjustment'] ?? 0),
                'total_amount'     => $total,
                'paid_amount'      => $paid,
                'balance'          => $balance,
                'notes'            => $zohoInv['notes'] ?? null,
                'terms'            => $zohoInv['terms'] ?? null,
            ];

            if ($localInv) {
                $localId = (int) $localInv['id'];
                $this->invoiceModel->skipValidation(true)->update($localId, $invData);
                $this->itemModel->where('invoice_id', $localId)->delete();
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->invoiceModel->skipValidation(true)->insert($invData, true);
                $action = 'CREATED';
            }

            foreach (($zohoInv['line_items'] ?? []) as $li) {
                $prodId   = $this->ensureLocalProductId($li['item_id'] ?? null, $li['name'] ?? null, $li['sku'] ?? null, (float) ($li['rate'] ?? 0), $li['hsn_or_sac'] ?? null);
                $taxRates = $this->taxMapper->extractLineTaxRates($li, (bool) $isInterState);
                $qty      = (float) ($li['quantity'] ?? 1);
                $rate     = (float) ($li['rate'] ?? 0);

                $desc = trim((string)($li['description'] ?? ''));
                if ($desc === '') {
                    $desc = trim((string)($li['name'] ?? ''));
                }

                $this->itemModel->insert([
                    'invoice_id'     => $localId,
                    'product_id'     => $prodId,
                    'description'    => $desc,
                    'hsn_code'       => $li['hsn_or_sac'] ?? null,
                    'quantity'       => $qty,
                    'rate'           => $rate,
                    'tax_percentage' => $taxRates['tax_percentage'],
                    'cgst_rate'      => $taxRates['cgst_rate'],
                    'sgst_rate'      => $taxRates['sgst_rate'],
                    'igst_rate'      => $taxRates['igst_rate'],
                    'amount'         => (float) ($li['item_total'] ?? ($qty * $rate)),
                ]);
            }

            // Post ledger entries for newly imported non-draft/non-void Zoho invoices
            if ($action === 'CREATED' && !in_array($localStatus, ['Draft', 'Void'], true)) {
                try {
                    $accountingModel = new AccountingModel();
                    $accountingModel->recordTransaction('invoice', $localId, $invData['invoice_date'], 'Zoho Invoice #' . $invData['invoice_number'], [
                        ['account_name' => 'Accounts Receivable', 'debit' => $total, 'credit' => 0],
                        ['account_name' => 'Sales Income', 'debit' => 0, 'credit' => round($total - $taxTotal, 2)],
                        ['account_name' => 'GST Payable', 'debit' => 0, 'credit' => $taxTotal],
                    ]);
                } catch (\Throwable $e) {
                    log_message('error', 'InvoiceSyncService ledger post error: ' . $e->getMessage());
                }
            }

            $updated         = $this->invoiceModel->getInvoiceById($localId);
            $normalizedLocal = $this->normalizeLocalInvoice($updated, $updated['items'] ?? []);

            // Synchronize e-Way Bill record if eway bill details exist in Zoho
            if (!empty($ewbNumber)) {
                try {
                    $ewayModel = new \App\Models\EwayBillModel();
                    $existingEwb = $ewayModel->where('document_type', 'invoice')
                        ->where('document_id', $localId)
                        ->first();

                    $validUntil = null;
                    if (!empty($ewbExpiry)) {
                        $parsedTime = strtotime($ewbExpiry);
                        if ($parsedTime !== false) {
                            $validUntil = date('Y-m-d H:i:s', $parsedTime);
                        }
                    }

                    $ewbDateFormatted = null;
                    if (!empty($ewbDate)) {
                        $parsedDate = strtotime($ewbDate);
                        if ($parsedDate !== false) {
                            $ewbDateFormatted = date('Y-m-d', $parsedDate);
                        }
                    }

                    $ewbStatusNormalized = 'generated';
                    if (!empty($ewbStatus) && strtolower($ewbStatus) === 'cancelled') {
                        $ewbStatusNormalized = 'cancelled';
                    }

                    $ewbPayload = [
                        'ewaybill_number'  => $ewbNumber,
                        'status'           => $ewbStatusNormalized,
                        'last_sync_at'     => date('Y-m-d H:i:s'),
                    ];
                    if (!empty($ewbId)) {
                        $ewbPayload['zoho_ewaybill_id'] = (string) $ewbId;
                    }
                    if ($validUntil) {
                        $ewbPayload['valid_until'] = $validUntil;
                    }
                    if ($ewbDateFormatted) {
                        $ewbPayload['ewaybill_date'] = $ewbDateFormatted;
                    }

                    if ($existingEwb) {
                        $ewayModel->update($existingEwb['id'], $ewbPayload);
                    } else {
                        $ewbPayload['document_type']   = 'invoice';
                        $ewbPayload['document_id']     = $localId;
                        $ewbPayload['document_number'] = $updated['invoice_number'];
                        $ewbPayload['document_date']   = $updated['invoice_date'];
                        $ewbPayload['total_amount']    = $updated['total_amount'];
                        $ewbPayload['cgst_amount']     = $updated['cgst_amount'];
                        $ewbPayload['sgst_amount']     = $updated['sgst_amount'];
                        $ewbPayload['igst_amount']     = $updated['igst_amount'];
                        $ewayModel->insert($ewbPayload);
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'InvoiceSyncService e-Way Bill sync error: ' . $e->getMessage());
                }
            }

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['invoice_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoInv['last_modified_time'] ?? null
            );

            // Automatically adjust invoice numbering sequence in settings if this pulled invoice has a higher sequence
            $this->adjustInvoiceSequenceAfterPull($updated['invoice_number'] ?? '');

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $invoice, array $items, string $zohoCustomerId): array
    {
        $isInterState  = !empty($invoice['is_inter_state']);
        $placeOfSupply = $this->resolveCustomerPlaceOfSupply((int) $invoice['customer_id'], $isInterState);

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
                $li['name'] = (string) ($item['description'] ?: 'Invoice Item');
            }
            if (!empty($item['hsn_code'])) {
                $li['hsn_or_sac'] = trim((string) $item['hsn_code']);
            }
            if ($zohoTaxId) {
                $li['tax_id'] = $zohoTaxId;
            }
            $lineItems[] = $li;
        }

        $discountVal = (float) ($invoice['discount_amount'] ?? 0);
        $discountStr = ($invoice['discount_type'] ?? 'Fixed') === 'Percentage'
            ? $discountVal . '%'
            : round($discountVal, 2);

        $payload = [
            'customer_id'             => $zohoCustomerId,
            'invoice_number'          => $invoice['invoice_number'],
            'date'                    => $invoice['invoice_date'],
            'due_date'                => $invoice['due_date'] ?: $invoice['invoice_date'],
            'place_of_supply'         => $placeOfSupply,
            'reference_number'        => (string) ($invoice['reference_number'] ?? ''),
            'line_items'              => $lineItems,
            'discount'                => $discountStr,
            'is_discount_before_tax'  => true,
            'discount_type'           => 'entity_level',
            'shipping_charge'         => round((float) ($invoice['shipping_charge'] ?? 0), 2),
            'adjustment'              => round((float) ($invoice['roundoff_amount'] ?? 0), 2),
            'adjustment_description'  => 'Round Off',
            'notes'                   => (string) ($invoice['notes'] ?? ''),
            'terms'                   => (string) ($invoice['terms'] ?? ''),
        ];

        // Zoho Books Custom Fields mapping (e.g. cf_no_of_bales)
        // Transport field is kept in local app only and not passed to Zoho Books invoices.
        $customFields = [];
        $balesCount = !empty($invoice['bales_count']) ? (int) $invoice['bales_count'] : (!empty($invoice['packages_count']) ? (int) $invoice['packages_count'] : null);
        if ($balesCount !== null) {
            $customFields[] = [
                'api_name' => 'cf_no_of_bales',
                'value'    => (int) $balesCount,
            ];
        }

        if (!empty($customFields)) {
            $payload['custom_fields'] = $customFields;
        }

        return $payload;
    }

    public function normalizeLocalInvoice(array $inv, array $items): array
    {
        $normalizedItems = [];
        foreach ($items as $item) {
            $normalizedItems[] = [
                'product_id'     => (int) ($item['product_id'] ?? 0),
                'description'    => trim((string) ($item['description'] ?? '')),
                'hsn_code'       => trim((string) ($item['hsn_code'] ?? '')),
                'quantity'       => round((float) ($item['quantity'] ?? 0), 2),
                'rate'           => round((float) ($item['rate'] ?? 0), 2),
                'tax_percentage' => round((float) ($item['tax_percentage'] ?? 0), 2),
            ];
        }

        return [
            'customer_id'      => (int) ($inv['customer_id'] ?? 0),
            'invoice_number'   => trim((string) ($inv['invoice_number'] ?? '')),
            'date'             => (string) ($inv['invoice_date'] ?? ''),
            'due_date'         => (string) ($inv['due_date'] ?? ''),
            'reference_number' => (string) ($inv['reference_number'] ?? ''),
            'is_inter_state'   => !empty($inv['is_inter_state']) ? 1 : 0,
            'subtotal'         => round((float) ($inv['subtotal'] ?? 0), 2),
            'discount_amount'  => round((float) ($inv['discount_amount'] ?? 0), 2),
            'discount_type'    => (string) ($inv['discount_type'] ?? 'Fixed'),
            'shipping_charge'  => round((float) ($inv['shipping_charge'] ?? 0), 2),
            'roundoff_amount'  => round((float) ($inv['roundoff_amount'] ?? 0), 2),
            'tax_amount'       => round((float) ($inv['tax_amount'] ?? 0), 2),
            'total_amount'     => round((float) ($inv['total_amount'] ?? 0), 2),
            'bales_count'      => (int) ($inv['bales_count'] ?? $inv['packages_count'] ?? 0),
            'notes'            => (string) ($inv['notes'] ?? ''),
            'terms'            => (string) ($inv['terms'] ?? ''),
            'items'            => $normalizedItems,
            'is_draft'         => (($inv['status'] ?? '') === 'Draft') ? 1 : 0,
        ];
    }

    public function normalizeZohoInvoice(array $z): array
    {
        return [
            'invoice_number'  => trim((string) ($z['invoice_number'] ?? '')),
            'date'            => (string) ($z['date'] ?? ''),
            'due_date'        => (string) ($z['due_date'] ?? ''),
            'subtotal'        => round((float) ($z['sub_total'] ?? 0), 2),
            'discount_amount' => round((float) ($z['discount_total'] ?? $z['discount'] ?? 0), 2),
            'tax_amount'      => round((float) ($z['tax_total'] ?? 0), 2),
            'total_amount'    => round((float) ($z['total'] ?? 0), 2),
            'items_count'     => isset($z['line_items']) && is_array($z['line_items']) ? count($z['line_items']) : 0,
            'is_draft'        => (strtolower((string) ($z['status'] ?? '')) === 'draft') ? 1 : 0,
        ];
    }

    /**
     * Pull a single invoice's details and line items directly from Zoho Books.
     * Guaranteed to populate line items and updated metadata locally.
     */
    public function pullSingleInvoiceFromZoho(int $localInvoiceId): array
    {
        $mapping = $this->mappingModel->findByLocal('invoices', $localInvoiceId);
        $zohoId  = !empty($mapping['zoho_record_id']) ? (string) $mapping['zoho_record_id'] : '';

        if ($zohoId === '') {
            // Check if we can find mapping by invoice number
            $inv = $this->invoiceModel->find($localInvoiceId);
            if ($inv && !empty($inv['invoice_number'])) {
                $byNum = $this->mappingModel->where('module', 'invoices')
                    ->where('zoho_record_number', trim($inv['invoice_number']))
                    ->first();
                if ($byNum && !empty($byNum['zoho_record_id'])) {
                    $zohoId = (string) $byNum['zoho_record_id'];
                }
            }
        }

        if ($zohoId === '') {
            return ['success' => false, 'message' => "No Zoho mapping found for local invoice #{$localInvoiceId}."];
        }

        $detailRes = $this->client->get("/invoices/{$zohoId}", [], [
            'module'         => 'invoices',
            'local_record_id'=> $localInvoiceId,
            'zoho_record_id' => $zohoId,
            'direction'      => 'FROM_ZOHO',
            'operation'      => 'GET_SINGLE_INVOICE_DETAIL',
        ]);

        if (empty($detailRes['success']) || empty($detailRes['data']['invoice'])) {
            return [
                'success' => false,
                'message' => $detailRes['message'] ?? 'Failed to fetch invoice details from Zoho Books.',
            ];
        }

        $zohoInv = $detailRes['data']['invoice'];
        return $this->applyZohoRecordToLocal($zohoInv, true);
    }

    /**
     * Ensure the invoice sequence setting stays at or above any newly pulled Zoho invoice number
     */
    protected function adjustInvoiceSequenceAfterPull(string $invoiceNumber): void
    {
        if (empty($invoiceNumber)) {
            return;
        }

        try {
            $settingModel = new \App\Models\SettingModel();
            $prefix = $settingModel->getSetting('invoice_prefix', 'INV-');
            $prefixEscaped = preg_quote($prefix, '/');

            if (preg_match('/^' . $prefixEscaped . '(\d+)$/', trim($invoiceNumber), $matches)) {
                $pulledNum = (int) $matches[1];
                $currentNext = (int) $settingModel->getSetting('invoice_next_number', '0');

                // If pulled invoice number is >= the current next number, advance next number to pulledNum + 1
                if ($pulledNum >= $currentNext) {
                    $settingModel->updateSetting('invoice_next_number', (string) ($pulledNum + 1));
                    log_message('info', "Adjusted invoice_next_number to " . ($pulledNum + 1) . " following Zoho pull of invoice {$invoiceNumber}");
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error adjusting invoice sequence after Zoho pull: ' . $e->getMessage());
        }
    }
}


