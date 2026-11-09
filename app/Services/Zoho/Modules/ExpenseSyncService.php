<?php

namespace App\Services\Zoho\Modules;

use App\Models\ExpenseCategoryModel;
use App\Models\ExpenseModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class ExpenseSyncService extends AbstractModuleSyncService
{
    protected ExpenseModel $expenseModel;
    protected ExpenseCategoryModel $categoryModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->expenseModel  = new ExpenseModel();
        $this->categoryModel = new ExpenseCategoryModel();
    }

    public function getModuleName(): string
    {
        return 'expenses';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('expenses', $localId);
        $expense = $this->expenseModel->find($localId);

        if ($operation === 'DELETE' || !$expense) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Expense was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            $delRes = $this->client->delete("/expenses/{$zohoId}", [], [
                'module'          => 'expenses',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'DELETE',
            ]);
            if (!empty($delRes['success'])) {
                $this->mappingModel->markStatus('expenses', $localId, 'DELETED_IN_ZOHO');
                return ['success' => true, 'message' => 'Expense deleted in Zoho Books.'];
            }
            $this->mappingModel->markStatus('expenses', $localId, 'FAILED', $delRes['message'] ?? 'Delete failed');
            return $delRes;
        }

        $normalizedLocal = $this->normalizeLocalExpense($expense);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Expense unchanged since last sync.',
                ];
            }
        }

        $expenseAccId = $this->taxMapper->resolveExpenseAccountId(
            !empty($expense['category_id']) ? (int) $expense['category_id'] : null,
            $expense['category'] ?? null
        );
        $paidThroughAccId = $this->taxMapper->resolvePaidThroughAccountId(
            !empty($expense['bank_account_id']) ? (int) $expense['bank_account_id'] : null,
            $expense['payment_mode'] ?? null
        );

        if (empty($expenseAccId)) {
            $msg = 'Unable to resolve Zoho Expense Account ID from Chart of Accounts.';
            $this->mappingModel->markStatus('expenses', $localId, 'FAILED', $msg);
            return ['success' => false, 'message' => $msg];
        }

        $payload = [
            'account_id'       => $expenseAccId,
            'date'             => $expense['expense_date'],
            'amount'           => round((float) ($expense['amount'] ?? 0), 2),
            'description'      => (string) ($expense['description'] ?? $expense['category'] ?? ''),
            'reference_number' => (string) ($expense['reference_number'] ?? ('EXP-' . $localId)),
        ];

        if ($paidThroughAccId) {
            $payload['paid_through_account_id'] = $paidThroughAccId;
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;

        if (!empty($zohoId)) {
            $res = $this->client->put("/expenses/{$zohoId}", $payload, [], [
                'module'          => 'expenses',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/expenses', $payload, [], [
                'module'          => 'expenses',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('expenses', $localId, 'FAILED', $res['message'] ?? 'Expense sync failed');
            return $res;
        }

        $zohoExp        = $res['data']['expense'] ?? [];
        $newZohoId      = (string) ($zohoExp['expense_id'] ?? $zohoId);
        $normalizedZoho = !empty($zohoExp) ? $this->normalizeZohoExpense($zohoExp) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $expense['reference_number'] ?: ('EXP-' . $localId),
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoExp['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Expense synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('expenses');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/expenses', 'expenses', $query, 50, [
            'module'    => 'expenses',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_EXPENSES',
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

        foreach ($listRes['records'] as $zohoExp) {
            $stats['total_processed']++;
            $applyRes = $this->applyZohoRecordToLocal($zohoExp, !$checkConflicts);
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

            if (!empty($zohoExp['last_modified_time'])) {
                $latestModified = $zohoExp['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('expenses', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoExp, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoExp['expense_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho expense_id'];
        }

        $mapping      = $this->mappingModel->findByZoho('expenses', $zohoId);
        $localExpense = null;

        if ($mapping) {
            $localExpense = $this->expenseModel->find((int) $mapping['local_record_id']);
        }
        if (!$localExpense && !empty($zohoExp['reference_number'])) {
            $localExpense = $this->expenseModel->where('reference_number', trim($zohoExp['reference_number']))->first();
        }

        $normalizedZoho = $this->normalizeZohoExpense($zohoExp);

        if ($localExpense && !$forceOverwrite && $mapping) {
            $localId         = (int) $localExpense['id'];
            $normalizedLocal = $this->normalizeLocalExpense($localExpense);

            $eval = $this->conflictService->evaluate(
                'expenses',
                $localId,
                $zohoId,
                $localExpense['reference_number'] ?: ('EXP-' . $localId),
                $normalizedLocal,
                $normalizedZoho,
                $localExpense['updated_at'] ?? null,
                $zohoExp['last_modified_time'] ?? null,
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

        return ZohoSyncContext::runAsZoho(function () use ($zohoExp, $zohoId, $localExpense, $normalizedZoho) {
            $catName = trim((string) ($zohoExp['account_name'] ?? 'General Expense'));
            $catRow  = $this->categoryModel->where('category_name', $catName)->first();
            if (!$catRow) {
                $catId = (int) $this->categoryModel->skipValidation(true)->insert([
                    'category_name' => $catName,
                    'description'   => 'Imported from Zoho Books Chart of Accounts',
                    'status'        => 'Active',
                ], true);
            } else {
                $catId = (int) $catRow['id'];
            }

            $expData = [
                'expense_date'     => $zohoExp['date'] ?? date('Y-m-d'),
                'category_id'      => $catId,
                'category'         => $catName,
                'amount'           => (float) ($zohoExp['total'] ?? $zohoExp['amount'] ?? 0),
                'description'      => $zohoExp['description'] ?? ($localExpense['description'] ?? $catName),
                'payment_mode'     => $localExpense['payment_mode'] ?? 'Cash',
                'reference_number' => $zohoExp['reference_number'] ?? ($localExpense['reference_number'] ?? ('ZEXP-' . substr($zohoId, -6))),
            ];

            if ($localExpense) {
                $localId = (int) $localExpense['id'];
                $this->expenseModel->skipValidation(true)->update($localId, $expData);
                $action = 'UPDATED';
            } else {
                $localId = (int) $this->expenseModel->skipValidation(true)->insert($expData, true);
                $action = 'CREATED';
            }

            $updated         = $this->expenseModel->find($localId);
            $normalizedLocal = $this->normalizeLocalExpense($updated);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updated['reference_number'] ?: ('EXP-' . $localId),
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoExp['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    public function normalizeLocalExpense(array $e): array
    {
        return [
            'expense_date'     => (string) ($e['expense_date'] ?? ''),
            'amount'           => round((float) ($e['amount'] ?? 0), 2),
            'reference_number' => trim((string) ($e['reference_number'] ?? '')),
            'description'      => trim((string) ($e['description'] ?? '')),
        ];
    }

    public function normalizeZohoExpense(array $z): array
    {
        return [
            'expense_date'     => (string) ($z['date'] ?? ''),
            'amount'           => round((float) ($z['total'] ?? $z['amount'] ?? 0), 2),
            'reference_number' => trim((string) ($z['reference_number'] ?? '')),
            'description'      => trim((string) ($z['description'] ?? '')),
        ];
    }
}

