<?php

namespace App\Controllers;

use App\Models\BankTransactionModel;
use App\Models\BankAccountModel;

class BankTransactionController extends BaseController
{
    protected $transactionModel;
    protected $accountModel;

    public function __construct()
    {
        $this->transactionModel = new BankTransactionModel();
        $this->accountModel = new BankAccountModel();
    }

    public function index($accountId)
    {
        $account = $this->accountModel->find($accountId);
        if (!$account) {
            return redirect()->to('bank_accounts')->with('error', 'Bank account not found.');
        }

        $filters = [
            'type'      => $this->request->getGet('type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $data['account'] = $account;
        $data['transactions'] = $this->transactionModel->getTransactionsWithFilters($accountId, $filters);
        $data['title'] = $account['bank_name'] . ' - Statement';
        $data['filters'] = $filters;
        
        return view('bank_accounts/statement', $data);
    }

    /**
     * Recalculate balance_after on all transactions for an account backward from its latest balance.
     */
    private function recalculateRunningBalances(int $accountId, float $latestBalance): void
    {
        $rows = $this->transactionModel
            ->where('bank_account_id', $accountId)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $running = round($latestBalance, 2);
        foreach ($rows as $row) {
            if (round((float) ($row['balance_after'] ?? 0), 2) !== $running) {
                $this->transactionModel->update($row['id'], ['balance_after' => $running]);
            }
            $amt = (float) ($row['amount'] ?? 0);
            $running = ($row['type'] === 'credit')
                ? round($running - $amt, 2)
                : round($running + $amt, 2);
        }
    }

    public function store()
    {
        $accountId = (int) $this->request->getPost('bank_account_id');
        $account = $this->accountModel->find($accountId);
        if (!$account) {
            return redirect()->back()->withInput()->with('error', 'Bank account not found.');
        }

        if (!$this->validate($this->transactionModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $type = $this->request->getPost('type');
        $amount = round((float) $this->request->getPost('amount'), 2);

        $newBalance = round((float) ($account['current_balance'] ?? 0), 2);
        $newBalance = ($type === 'credit')
            ? round($newBalance + $amount, 2)
            : round($newBalance - $amount, 2);

        $transactionData = $this->request->getPost();
        $transactionData['amount'] = $amount;
        $transactionData['balance_after'] = $newBalance;

        $this->transactionModel->save($transactionData);
        $this->accountModel->update($accountId, ['current_balance' => $newBalance]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Transaction failed.');
        }

        return redirect()->to('bank_accounts/statement/' . $accountId)->with('success', 'Transaction recorded successfully.');
    }

    public function edit($id)
    {
        $transaction = $this->transactionModel->find($id);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        $data['transaction'] = $transaction;
        $data['account'] = $this->accountModel->find($transaction['bank_account_id']);
        $data['title'] = 'Edit Transaction';
        
        return view('bank_accounts/edit_transaction', $data);
    }

    public function update($id)
    {
        $oldTransaction = $this->transactionModel->find($id);
        if (!$oldTransaction) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        if (!$this->validate($this->transactionModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $accountId = (int) $oldTransaction['bank_account_id'];
        $account = $this->accountModel->find($accountId);

        // Reverse old transaction
        $balance = round((float) ($account['current_balance'] ?? 0), 2);
        $oldAmount = round((float) ($oldTransaction['amount'] ?? 0), 2);
        $balance = ($oldTransaction['type'] === 'credit')
            ? round($balance - $oldAmount, 2)
            : round($balance + $oldAmount, 2);

        // Apply new transaction
        $newType = $this->request->getPost('type');
        $newAmount = round((float) $this->request->getPost('amount'), 2);
        $balance = ($newType === 'credit')
            ? round($balance + $newAmount, 2)
            : round($balance - $newAmount, 2);

        // If reconciled and amount/type changed, unmatch linked payment to avoid stale reconciliation
        if (!empty($oldTransaction['is_reconciled']) && ($oldTypeChanged = ($newType !== $oldTransaction['type'] || $newAmount !== $oldAmount))) {
            (new \App\Models\ReconciliationModel())->unmatchTransaction($id);
        }

        $transactionData = $this->request->getPost();
        $transactionData['amount'] = $newAmount;
        $transactionData['balance_after'] = $balance;

        $this->transactionModel->update($id, $transactionData);
        $this->accountModel->update($accountId, ['current_balance' => $balance]);
        $this->recalculateRunningBalances($accountId, $balance);

        $db->transComplete();

        return redirect()->to('bank_accounts/statement/' . $accountId)->with('success', 'Transaction updated successfully.');
    }

    public function delete($id)
    {
        $transaction = $this->transactionModel->find($id);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Unmatch linked payment first if this transaction was reconciled
        if (!empty($transaction['is_reconciled'])) {
            (new \App\Models\ReconciliationModel())->unmatchTransaction($id);
        }

        $accountId = (int) $transaction['bank_account_id'];
        $account = $this->accountModel->find($accountId);

        $balance = round((float) ($account['current_balance'] ?? 0), 2);
        $amt = round((float) ($transaction['amount'] ?? 0), 2);
        $balance = ($transaction['type'] === 'credit')
            ? round($balance - $amt, 2)
            : round($balance + $amt, 2);

        $this->transactionModel->delete($id);
        $this->accountModel->update($accountId, ['current_balance' => $balance]);
        $this->recalculateRunningBalances($accountId, $balance);

        $db->transComplete();

        return redirect()->to('bank_accounts/statement/' . $accountId)->with('success', 'Transaction deleted successfully.');
    }
}
