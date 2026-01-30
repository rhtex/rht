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

    public function store()
    {
        $accountId = $this->request->getPost('bank_account_id');
        
        if (!$this->validate($this->transactionModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $account = $this->accountModel->find($accountId);
        $type = $this->request->getPost('type');
        $amount = $this->request->getPost('amount');
        
        $newBalance = $account['current_balance'];
        if ($type === 'credit') {
            $newBalance += $amount;
        } else {
            $newBalance -= $amount;
        }

        $transactionData = $this->request->getPost();
        $transactionData['balance_after'] = $newBalance;

        $this->transactionModel->save($transactionData);
        
        // Update account balance
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

        $accountId = $oldTransaction['bank_account_id'];
        $account = $this->accountModel->find($accountId);
        
        // Reverse old transaction
        $balance = $account['current_balance'];
        if ($oldTransaction['type'] === 'credit') {
            $balance -= $oldTransaction['amount'];
        } else {
            $balance += $oldTransaction['amount'];
        }

        // Apply new transaction
        $newType = $this->request->getPost('type');
        $newAmount = $this->request->getPost('amount');
        if ($newType === 'credit') {
            $balance += $newAmount;
        } else {
            $balance -= $newAmount;
        }

        $transactionData = $this->request->getPost();
        $transactionData['balance_after'] = $balance;

        $this->transactionModel->update($id, $transactionData);
        $this->accountModel->update($accountId, ['current_balance' => $balance]);

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

        $accountId = $transaction['bank_account_id'];
        $account = $this->accountModel->find($accountId);
        
        $balance = $account['current_balance'];
        if ($transaction['type'] === 'credit') {
            $balance -= $transaction['amount'];
        } else {
            $balance += $transaction['amount'];
        }

        $this->transactionModel->delete($id);
        $this->accountModel->update($accountId, ['current_balance' => $balance]);

        $db->transComplete();

        return redirect()->to('bank_accounts/statement/' . $accountId)->with('success', 'Transaction deleted successfully.');
    }
}
