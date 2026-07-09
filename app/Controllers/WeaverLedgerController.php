<?php

namespace App\Controllers;

use App\Models\WeaverLedgerModel;
use App\Models\WeaverLedgerTransactionModel;

class WeaverLedgerController extends BaseController
{
    protected $ledgerModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->ledgerModel = new WeaverLedgerModel();
        $this->transactionModel = new WeaverLedgerTransactionModel();
    }

    public function store()
    {
        $data = $this->request->getPost();
        
        $weaverId = $data['weaver_id'];
        $principalAmount = $data['principal_amount'];
        $interestRate = $data['interest_rate'];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $ledgerData = [
                'weaver_id' => $weaverId,
                'title' => $data['title'],
                'principal_amount' => $principalAmount,
                'interest_rate' => $interestRate,
                'total_amount_due' => $principalAmount,
                'balance_amount' => $principalAmount,
                'status' => 'Active',
                'created_by' => session('user_id'),
            ];

            if ($this->ledgerModel->insert($ledgerData)) {
                $ledgerId = $this->ledgerModel->getInsertID();

                $transactionData = [
                    'ledger_id' => $ledgerId,
                    'transaction_type' => 'Principal',
                    'amount' => $principalAmount,
                    'transaction_date' => date('Y-m-d'),
                    'remarks' => 'Initial Advance/Loan',
                    'created_by' => session('user_id'),
                ];
                $this->transactionModel->insert($transactionData);
            }
            
            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Failed to create personal ledger.');
            }

            return redirect()->back()->with('success', 'Personal Ledger initialized successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        
        $ledger = $this->ledgerModel->find($id);
        if (!$ledger) {
            return redirect()->back()->with('error', 'Ledger not found.');
        }

        $oldPrincipal = $ledger['principal_amount'];
        $newPrincipal = $data['principal_amount'];
        $diff = $newPrincipal - $oldPrincipal;

        $updateData = [
            'title' => $data['title'],
            'principal_amount' => $newPrincipal,
            'interest_rate' => $data['interest_rate'],
            'updated_by' => session('user_id')
        ];

        if ($diff != 0) {
            $updateData['total_amount_due'] = $ledger['total_amount_due'] + $diff;
            $updateData['balance_amount'] = $ledger['balance_amount'] + $diff;
            $updateData['status'] = ($updateData['balance_amount'] <= 0) ? 'Cleared' : 'Active';
            
            $principalTxn = $this->transactionModel->where('ledger_id', $id)->where('transaction_type', 'Principal')->first();
            if ($principalTxn) {
                $this->transactionModel->update($principalTxn['id'], ['amount' => $newPrincipal]);
            }
        }

        if ($this->ledgerModel->update($id, $updateData)) {
            return redirect()->back()->with('success', 'Ledger updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update ledger.');
    }

    public function delete($id)
    {
        if ($this->ledgerModel->delete($id)) {
            return redirect()->back()->with('success', 'Ledger deleted successfully.');
        }
        return redirect()->back()->with('error', 'Failed to delete ledger.');
    }

    public function addPayment($ledgerId)
    {
        $data = $this->request->getPost();
        
        $ledger = $this->ledgerModel->find($ledgerId);
        if (!$ledger) {
            return redirect()->back()->with('error', 'Ledger not found.');
        }

        $amount = (float) $data['amount'];
        $transactionType = $data['transaction_type'];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $transactionData = [
                'ledger_id' => $ledgerId,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'remarks' => $data['remarks'],
                'created_by' => session('user_id'),
            ];

            $this->transactionModel->insert($transactionData);

            if ($transactionType == 'Payment' || $transactionType == 'Waiveoff') {
                $newBalance = $ledger['balance_amount'] - $amount;
            } elseif ($transactionType == 'Interest Addition') {
                $newBalance = $ledger['balance_amount'] + $amount;
                $newTotal = $ledger['total_amount_due'] + $amount;
                $this->ledgerModel->update($ledgerId, ['total_amount_due' => $newTotal]);
            }

            $status = ($newBalance <= 0) ? 'Cleared' : 'Active';
            $this->ledgerModel->update($ledgerId, [
                'balance_amount' => $newBalance,
                'status' => $status
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Failed to record transaction.');
            }

            return redirect()->back()->with('success', 'Transaction recorded successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function reverseTransaction($transactionId)
    {
        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        $ledgerId = $transaction['ledger_id'];
        $ledger = $this->ledgerModel->find($ledgerId);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $amount = $transaction['amount'];
            
            $reversalData = [
                'ledger_id' => $ledgerId,
                'transaction_type' => 'Reversal',
                'amount' => $amount,
                'payment_method' => $transaction['payment_method'],
                'transaction_date' => date('Y-m-d'),
                'remarks' => 'Reversal of transaction ID ' . $transactionId,
                'created_by' => session('user_id'),
            ];
            $this->transactionModel->insert($reversalData);

            if ($transaction['transaction_type'] == 'Payment' || $transaction['transaction_type'] == 'Waiveoff') {
                $newBalance = $ledger['balance_amount'] + $amount;
            } elseif ($transaction['transaction_type'] == 'Interest Addition') {
                $newBalance = $ledger['balance_amount'] - $amount;
                $newTotal = $ledger['total_amount_due'] - $amount;
                $this->ledgerModel->update($ledgerId, ['total_amount_due' => $newTotal]);
            } else {
                 return redirect()->back()->with('error', 'Cannot reverse this type of transaction.');
            }

            $status = ($newBalance <= 0) ? 'Cleared' : 'Active';
            $this->ledgerModel->update($ledgerId, [
                'balance_amount' => $newBalance,
                'status' => $status
            ]);
            
            $this->transactionModel->delete($transactionId);
            
            $db->transComplete();

            return redirect()->back()->with('success', 'Transaction reversed successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
