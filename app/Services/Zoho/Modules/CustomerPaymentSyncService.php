<?php

namespace App\Services\Zoho\Modules;

use App\Models\InvoiceModel;
use App\Models\InvoicePaymentModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class CustomerPaymentSyncService extends AbstractModuleSyncService
{
    protected InvoicePaymentModel $paymentModel;
    protected InvoiceModel $invoiceModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->paymentModel = new InvoicePaymentModel();
        $this->invoiceModel = new InvoiceModel();
    }

    public function getModuleName(): string
    {
        return 'customer_payments';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('customer_payments', $localId);
        $payment = $this->paymentModel->find($localId);

        if ($operation === 'DELETE' || !$payment) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Customer Receipt was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/customerpayments/{$zohoId}", [], [
                'module'          => 'customer_payments',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('customer_payments', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Customer Receipt deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('customer_payments', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $normalizedLocal = $this->normalizeLocalPayment($payment);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Customer Receipt unchanged since last sync.',
                ];
            }
        }

        $zohoCustomerId = $this->ensureZohoCustomerId((int) $payment['customer_id']);
        if (empty($zohoCustomerId)) {
            $msg = 'Unable to sync Customer #' . $payment['customer_id'] . ' to Zoho Books for Receipt.';
            $this->mappingModel->markStatus('customer_payments', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $zohoInvoiceId = null;
        if (!empty($payment['invoice_id'])) {
            $invMap = $this->mappingModel->findByLocal('invoices', (int) $payment['invoice_id']);
            if ($invMap && !empty($invMap['zoho_record_id'])) {
                $zohoInvoiceId = (string) $invMap['zoho_record_id'];
            } else {
                $invSync = new InvoiceSyncService($this->client);
                $invRes  = $invSync->pushToZoho((int) $payment['invoice_id'], 'CREATE');
                $zohoInvoiceId = $invRes['zoho_record_id'] ?? null;
            }
        }

        $zohoId  = $mapping['zoho_record_id'] ?? null;
        $payload = $this->buildZohoPayload($payment, $zohoCustomerId, $zohoInvoiceId);

        if (!empty($zohoId)) {
            $res = $this->client->put("/customerpayments/{$zohoId}", $payload, [], [
                'module'          => 'customer_payments',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/customerpayments', $payload, [], [
                'module'          => 'customer_payments',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('customer_payments', $localId, 'FAILED', $res['message'] ?? 'Receipt sync failed');
            return $res;
        }

        $zohoPay        = $res['data']['payment'] ?? [];
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
            'message'        => 'Customer Receipt synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('customer_payments');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/customerpayments', 'customerpayments', $query, 50, [
            'module'    => 'customer_payments',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_CUSTOMER_PAYMENTS',
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
            $zohoId = (string) ($summary['payment_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('customer_payments', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            $detailRes = $this->client->get("/customerpayments/{$zohoId}", [], [
                'module'         => 'customer_payments',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_CUSTOMER_PAYMENT_DETAIL',
            ]);

            $zohoPay  = !empty($detailRes['success']) ? ($detailRes['data']['payment'] ?? $summary) : $summary;
            $applyRes = $this->applyZohoRecordToLocal($zohoPay, !$checkConflicts);
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

            if (!empty($zohoPay['last_modified_time'])) {
                $latestModified = $zohoPay['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('customer_payments', [
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

        $mapping      = $this->mappingModel->findByZoho('customer_payments', $zohoId);
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
                'customer_payments',
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

        $localCustomerId = $this->ensureLocalCustomerId((string) ($zohoPay['customer_id'] ?? ''), $zohoPay['customer_name'] ?? null);
        if (!$localCustomerId) {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Could not resolve local customer for Zoho Payment ' . $zohoId];
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoPay, $zohoId, $localPayment, $localCustomerId, $normalizedZoho) {
            $localInvoiceId = $localPayment['invoice_id'] ?? null;
            $discountAmount = 0.0;

            if (!empty($zohoPay['invoices'][0]['invoice_id'])) {
                $zInvId       = (string) $zohoPay['invoices'][0]['invoice_id'];
                $discountAmount = (float) ($zohoPay['invoices'][0]['discount_amount'] ?? 0);
                $invMap       = $this->mappingModel->findByZoho('invoices', $zInvId);
                if ($invMap) {
                    $localInvoiceId = (int) $invMap['local_record_id'];
                }
            }

            $amount          = (float) ($zohoPay['amount'] ?? 0);
            $unappliedAmount = (float) ($zohoPay['unused_amount'] ?? ($localInvoiceId ? 0.0 : $amount));
            $paymentType     = $localInvoiceId ? 'invoice_payment' : 'customer_advance';

            $payData = [
                'invoice_id'       => $localInvoiceId,
                'customer_id'      => $localCustomerId,
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

            // Recalculate linked local invoice paid_amount & balance
            if ($localInvoiceId) {
                $this->recalculateLocalInvoiceBalance($localInvoiceId);
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

    protected function buildZohoPayload(array $payment, string $zohoCustomerId, ?string $zohoInvoiceId): array
    {
        $amount     = round((float) ($payment['amount'] ?? 0), 2);
        $deductions = round(
            (float) ($payment['discount_amount'] ?? 0)
            + (float) ($payment['mahimai_amount'] ?? 0)
            + (float) ($payment['postal_charges'] ?? 0),
            2
        );

        $accountId = $this->taxMapper->resolvePaidThroughAccountId(
            !empty($payment['bank_account_id']) ? (int) $payment['bank_account_id'] : null,
            $payment['payment_mode'] ?? null
        );

        $payload = [
            'customer_id'      => $zohoCustomerId,
            'payment_mode'     => (string) ($payment['payment_mode'] ?: 'Bank Transfer'),
            'amount'           => $amount,
            'date'             => $payment['payment_date'],
            'reference_number' => (string) ($payment['reference_number'] ?? $payment['payment_number'] ?? ''),
            'description'      => (string) ($payment['notes'] ?? ''),
        ];

        if ($accountId) {
            $payload['account_id'] = $accountId;
        }

        if ($zohoInvoiceId) {
            $payload['invoices'] = [
                [
                    'invoice_id'      => $zohoInvoiceId,
                    'amount_applied'  => $amount,
                    'discount_amount' => $deductions,
                ],
            ];
        }

        return $payload;
    }

    protected function recalculateLocalInvoiceBalance(int $invoiceId): void
    {
        $inv = $this->invoiceModel->find($invoiceId);
        if (!$inv) {
            return;
        }

        $payments = $this->paymentModel->where('invoice_id', $invoiceId)->findAll();
        $totalSettled = 0.0;
        foreach ($payments as $p) {
            $totalSettled += (float) ($p['amount'] ?? 0)
                + (float) ($p['discount_amount'] ?? 0)
                + (float) ($p['mahimai_amount'] ?? 0)
                + (float) ($p['postal_charges'] ?? 0);
        }

        $total   = (float) ($inv['total_amount'] ?? 0);
        $balance = max(0.0, round($total - $totalSettled, 2));
        $status  = $inv['status'];
        if ($status !== 'Void') {
            if ($balance <= 0.009) {
                $status = 'Paid';
            } elseif ($totalSettled > 0) {
                $status = 'Partially Paid';
            } else {
                $status = 'Open';
            }
        }

        $this->invoiceModel->skipValidation(true)->update($invoiceId, [
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

