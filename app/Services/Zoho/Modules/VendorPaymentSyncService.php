<?php

namespace App\Services\Zoho\Modules;

use App\Models\BillModel;
use App\Models\PaymentModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class VendorPaymentSyncService extends AbstractModuleSyncService
{
    protected PaymentModel $paymentModel;
    protected BillModel $billModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->paymentModel = new PaymentModel();
        $this->billModel    = new BillModel();
    }

    public function getModuleName(): string
    {
        return 'vendor_payments';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('vendor_payments', $localId);
        $payment = $this->paymentModel->find($localId);

        if ($operation === 'DELETE' || !$payment) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Vendor Payment was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/vendorpayments/{$zohoId}", [], [
                'module'          => 'vendor_payments',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('vendor_payments', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Vendor Payment deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('vendor_payments', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $normalizedLocal = $this->normalizeLocalPayment($payment);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Vendor Payment unchanged since last sync.',
                ];
            }
        }

        $zohoVendorId = $this->ensureZohoVendorId((int) $payment['vendor_id']);
        if (empty($zohoVendorId)) {
            $msg = 'Unable to sync Vendor #' . $payment['vendor_id'] . ' to Zoho Books for Payment.';
            $this->mappingModel->markStatus('vendor_payments', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoBillId = null;
        if (!empty($payment['bill_id'])) {
            $billMap = $this->mappingModel->findByLocal('bills', (int) $payment['bill_id']);
            if ($billMap && !empty($billMap['zoho_record_id'])) {
                $zohoBillId = (string) $billMap['zoho_record_id'];
            } else {
                $billSync = new BillSyncService($this->client);
                $billRes  = $billSync->pushToZoho((int) $payment['bill_id'], 'CREATE');
                $zohoBillId = $billRes['zoho_record_id'] ?? null;
            }
        }

        $zohoId  = $mapping['zoho_record_id'] ?? null;
        $payload = $this->buildZohoPayload($payment, $zohoVendorId, $zohoBillId);

        if (!empty($zohoId)) {
            $res = $this->client->put("/vendorpayments/{$zohoId}", $payload, [], [
                'module'          => 'vendor_payments',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/vendorpayments', $payload, [], [
                'module'          => 'vendor_payments',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('vendor_payments', $localId, 'FAILED', $res['message'] ?? 'Vendor Payment sync failed');
            return $res;
        }

        $zohoPay        = $res['data']['vendorpayment'] ?? [];
        $newZohoId      = (string) ($zohoPay['payment_id'] ?? $zohoId);
        $normalizedZoho = !empty($zohoPay) ? $this->normalizeZohoPayment($zohoPay) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $payment['payment_number'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoPay['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Vendor Payment synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('vendor_payments');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/vendorpayments', 'vendorpayments', $query, 50, [
            'module'    => 'vendor_payments',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_VENDOR_PAYMENTS',
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
        $this->emitProgress(15, "Retrieved {$totalRecords} vendor payment(s) from Zoho Books. Starting import...", ['total' => $totalRecords]);

        foreach ($listRes['records'] as $summary) {
            $idx++;
            $stats['total_processed']++;
            $zohoId = (string) ($summary['payment_id'] ?? '');
            $payNum = $summary['payment_number'] ?? $zohoId;
            $pct    = 15 + (int) round(($idx / max(1, $totalRecords)) * 80);

            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('vendor_payments', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Skipped unchanged payment #{$payNum}", ['processed' => $idx, 'total' => $totalRecords]);
                continue;
            }

            $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Importing payment #{$payNum}...", ['processed' => $idx, 'total' => $totalRecords]);

            $detailRes = $this->client->get("/vendorpayments/{$zohoId}", [], [
                'module'         => 'vendor_payments',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_VENDOR_PAYMENT_DETAIL',
            ]);

            $zohoPay  = !empty($detailRes['success']) ? ($detailRes['data']['vendorpayment'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoPay, !$checkConflicts);
            $act      = $applyRes['action'] ?? 'SKIPPED';
            if ($act === 'CREATED') {
                $stats['created_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Created payment #{$payNum}", ['action' => 'CREATED']);
            } elseif ($act === 'UPDATED') {
                $stats['updated_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Updated payment #{$payNum}", ['action' => 'UPDATED']);
            } elseif ($act === 'CONFLICT') {
                $stats['conflict_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Conflict detected on payment #{$payNum}", ['action' => 'CONFLICT']);
            } elseif ($act === 'FAILED') {
                $stats['failed_count']++;
                $this->emitProgress($pct, "[{$idx}/{$totalRecords}] Failed payment #{$payNum}: " . ($applyRes['message'] ?? 'Error'), ['action' => 'FAILED']);
            } else {
                $stats['skipped_count']++;
            }

            if (!empty($zohoPay['last_modified_time'])) {
                $latestModified = $zohoPay['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('vendor_payments', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoPay, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoPay['payment_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho payment_id'];
        }

        $mapping      = $this->mappingModel->findByZoho('vendor_payments', $zohoId);
        $localPayment = null;

        if ($mapping) {
            $localPayment = $this->paymentModel->find((int) $mapping['local_record_id']);
        }
        if (!$localPayment && !empty($zohoPay['payment_number'])) {
            $localPayment = $this->paymentModel->where('payment_number', trim($zohoPay['payment_number']))->first();
        }

        $normalizedZoho = $this->normalizeZohoPayment($zohoPay);

        if ($localPayment && !$forceOverwrite && $mapping) {
            $localId         = (int) $localPayment['id'];
            $normalizedLocal = $this->normalizeLocalPayment($localPayment);

            $eval = $this->conflictService->evaluate(
                'vendor_payments',
                $localId,
                $zohoId,
                $localPayment['payment_number'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localPayment['updated_at'] ?? null,
                $zohoPay['last_modified_time'] ?? null,
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

        $localVendorId = $this->ensureLocalVendorId((string) ($zohoPay['vendor_id'] ?? ''), $zohoPay['vendor_name'] ?? null);
        if (!$localVendorId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local vendor for Zoho Vendor Payment ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoPay, $zohoId, $localPayment, $localVendorId, $normalizedZoho) {
            $localBillId    = $localPayment['bill_id'] ?? null;
            $discountAmount = 0.0;

            if (!empty($zohoPay['bills'][0]['bill_id'])) {
                $zBillId        = (string) $zohoPay['bills'][0]['bill_id'];
                $discountAmount = (float) ($zohoPay['bills'][0]['discount_amount'] ?? 0);
                $billMap        = $this->mappingModel->findByZoho('bills', $zBillId);
                if ($billMap) {
                    $localBillId = (int) $billMap['local_record_id'];
                }
            }

            $amount          = (float) ($zohoPay['amount'] ?? 0);
            $unappliedAmount = (float) ($zohoPay['unused_amount'] ?? ($localBillId ? 0.0 : $amount));
            $paymentType     = $localBillId ? 'bill_payment' : 'supplier_advance';

            $payData = [
                'bill_id'          => $localBillId,
                'vendor_id'        => $localVendorId,
                'payment_type'     => $paymentType,
                'payment_number'   => trim((string) ($zohoPay['payment_number'] ?? ($localPayment['payment_number'] ?? $this->paymentModel->generatePaymentNumber()))),
                'payment_date'     => $zohoPay['date'] ?? date('Y-m-d'),
                'payment_mode'     => $zohoPay['payment_mode'] ?? ($localPayment['payment_mode'] ?? 'Bank Transfer'),
                'amount'           => $amount,
                'unapplied_amount' => $unappliedAmount,
                'discount_amount'  => $discountAmount ?: ($localPayment['discount_amount'] ?? 0),
                'reference_number' => $zohoPay['reference_number'] ?? null,
                'notes'            => $zohoPay['description'] ?? ($localPayment['notes'] ?? null),
            ];

            if ($localPayment) {
                $localId = (int) $localPayment['id'];
                $this->paymentModel->skipValidation(true)->update($localId, $payData);
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->paymentModel->skipValidation(true)->insert($payData, true);
                $action = 'CREATED';
            }

            if ($localBillId) {
                $this->recalculateLocalBillBalance($localBillId);
            }

            $updated         = $this->paymentModel->find($localId);
            $normalizedLocal = $this->normalizeLocalPayment($updated);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['payment_number'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoPay['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $payment, string $zohoVendorId, ?string $zohoBillId): array
    {
        $amount     = round((float) ($payment['amount'] ?? 0), 2);
        $deductions = round(
            (float) ($payment['discount_amount'] ?? 0)
            + (float) ($payment['mahimai_amount'] ?? 0)
            + (float) ($payment['postal_charges'] ?? 0),
            2
        );

        $paidThroughAccId = $this->taxMapper->resolvePaidThroughAccountId(
            !empty($payment['bank_account_id']) ? (int) $payment['bank_account_id'] : null,
            $payment['payment_mode'] ?? null
        );

        $payload = [
            'vendor_id'        => $zohoVendorId,
            'payment_number'   => (string) ($payment['payment_number'] ?? ''),
            'payment_mode'     => (string) ($payment['payment_mode'] ?: 'Bank Transfer'),
            'amount'           => $amount,
            'date'             => $payment['payment_date'],
            'reference_number' => (string) ($payment['reference_number'] ?? $payment['payment_number'] ?? ''),
            'description'      => (string) ($payment['notes'] ?? ''),
        ];

        if ($paidThroughAccId) {
            $payload['paid_through_account_id'] = $paidThroughAccId;
        }

        if ($zohoBillId) {
            $payload['bills'] = [
                [
                    'bill_id'         => $zohoBillId,
                    'amount_applied'  => $amount,
                    'discount_amount' => $deductions,
                ],
            ];
        }

        return $payload;
    }

    protected function recalculateLocalBillBalance(int $billId): void
    {
        $bill = $this->billModel->find($billId);
        if (!$bill) {
            return;
        }

        $payments = $this->paymentModel->where('bill_id', $billId)->findAll();
        $totalSettled = 0.0;
        foreach ($payments as $p) {
            $totalSettled += (float) ($p['amount'] ?? 0)
                + (float) ($p['discount_amount'] ?? 0)
                + (float) ($p['mahimai_amount'] ?? 0)
                + (float) ($p['postal_charges'] ?? 0);
        }

        $total   = (float) ($bill['total_amount'] ?? 0);
        $balance = max(0.0, round($total - $totalSettled, 2));
        $status  = $bill['status'];
        if ($status !== 'Void') {
            if ($balance <= 0.009) {
                $status = 'Paid';
            } elseif ($totalSettled > 0) {
                $status = 'Partially Paid';
            } else {
                $status = 'Open';
            }
        }

        $this->billModel->skipValidation(true)->update($billId, [
            'paid_amount' => round($totalSettled, 2),
            'balance'     => $balance,
            'status'      => $status,
        ]);
    }

    public function normalizeLocalPayment(array $p): array
    {
        return [
            'payment_date'     => (string) ($p['payment_date'] ?? ''),
            'payment_mode'     => strtolower(trim((string) ($p['payment_mode'] ?? ''))),
            'amount'           => round((float) ($p['amount'] ?? 0), 2),
            'reference_number' => trim((string) ($p['reference_number'] ?? '')),
        ];
    }

    public function normalizeZohoPayment(array $z): array
    {
        return [
            'payment_date'     => (string) ($z['date'] ?? ''),
            'payment_mode'     => strtolower(trim((string) ($z['payment_mode'] ?? ''))),
            'amount'           => round((float) ($z['amount'] ?? 0), 2),
            'reference_number' => trim((string) ($z['reference_number'] ?? '')),
        ];
    }
}

