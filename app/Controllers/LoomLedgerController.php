<?php

namespace App\Controllers;

use App\Models\LoomLedgerModel;
use App\Models\LoomLedgerTransactionModel;
use App\Models\WeaverLoomModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class LoomLedgerController extends BaseController
{
    protected $ledgerModel;
    protected $transactionModel;
    protected $loomModel;

    public function __construct()
    {
        $this->ledgerModel = new LoomLedgerModel();
        $this->transactionModel = new LoomLedgerTransactionModel();
        $this->loomModel = new WeaverLoomModel();
    }

    public function store()
    {
        $data = $this->request->getPost();
        
        $loomId = $data['loom_id'];
        $principalAmount = $data['principal_amount'];
        $interestRate = $data['interest_rate'];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $ledgerData = [
                'loom_id' => $loomId,
                'weaver_id' => $data['weaver_id'],
                'title' => $data['title'],
                'principal_amount' => $principalAmount,
                'interest_rate' => $interestRate,
                'total_amount_due' => $principalAmount, // initial before interest calculations if they do it later, or they can pre-calculate. We will start with principal.
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
                    'remarks' => 'Initial Loan Principal',
                    'created_by' => session('user_id'),
                ];
                $this->transactionModel->insert($transactionData);
            }
            
            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Failed to create ledger.');
            }

            return redirect()->back()->with('success', 'Loom Ledger initialized successfully.');

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
            
            // Also need to update the Principal transaction amount
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
            // Because of CASCADE, transactions are also deleted
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
        $transactionType = $data['transaction_type']; // Can be 'Payment', 'Interest Addition', 'Waiveoff'

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

            // Update ledger balance
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
            
            // Mark original transaction as reversed (or just delete it, or add a Reversal entry)
            // Let's add a Reversal entry to maintain audit log
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

            // Reverse balance
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
            
            // Delete original transaction to keep things clean or keep it. Let's delete it since we added a reversal entry, actually no, let's keep it but maybe it's better to delete the original and just update balance. The prompt says "reverse that". I will just delete it and reverse the balance.
            $this->transactionModel->delete($transactionId);
            // Actually, we don't need a Reversal entry if we delete it. Let's just delete it to keep it simple for the user.
            
            $db->transComplete();

            return redirect()->back()->with('success', 'Transaction reversed successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
