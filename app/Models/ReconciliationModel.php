<?php

namespace App\Models;

use CodeIgniter\Model;

class ReconciliationModel extends Model
{
    /**
     * Get unmatched credit bank transactions
     */
    public function getUnmatchedCreditTransactions($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('bank_transactions')
                      ->where('bank_account_id', $accountId)
                      ->where('type', 'credit')
                      ->where('is_reconciled', 0);
        
        if ($startDate) {
            $builder->where('transaction_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('transaction_date <=', $endDate);
        }
        
        return $builder->orderBy('transaction_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get unmatched debit bank transactions
     */
    public function getUnmatchedDebitTransactions($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('bank_transactions')
                      ->where('bank_account_id', $accountId)
                      ->where('type', 'debit')
                      ->where('is_reconciled', 0);
        
        if ($startDate) {
            $builder->where('transaction_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('transaction_date <=', $endDate);
        }
        
        return $builder->orderBy('transaction_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get unmatched invoice payments
     */
    public function getUnmatchedInvoicePayments($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('invoice_payments')
                      ->select('invoice_payments.*, invoices.invoice_number, customers.name as customer_name')
                      ->join('invoices', 'invoices.id = invoice_payments.invoice_id')
                      ->join('customers', 'customers.id = invoice_payments.customer_id')
                      ->where('invoice_payments.bank_account_id', $accountId)
                      ->where('invoice_payments.bank_transaction_id IS NULL');
        
        if ($startDate) {
            $builder->where('invoice_payments.payment_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('invoice_payments.payment_date <=', $endDate);
        }
        
        return $builder->orderBy('invoice_payments.payment_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get ALL unmatched outgoing payments from all modules
     */
    public function getAllUnmatchedOutgoingPayments($accountId, $startDate = null, $endDate = null)
    {
        $payments = [];
        
        // Get vendor payments
        $vendorPayments = $this->getUnmatchedVendorPayments($accountId, $startDate, $endDate);
        foreach ($vendorPayments as $payment) {
            $payments[] = [
                'id' => $payment['id'],
                'type' => 'payment',
                'type_label' => 'Vendor Payment',
                'date' => $payment['payment_date'],
                'amount' => $payment['amount'],
                'description' => 'Bill: ' . $payment['bill_number'] . ' - Vendor: ' . $payment['vendor_name'],
                'reference' => $payment['payment_number'],
            ];
        }
        
        // Get expenses
        $expenses = $this->getUnmatchedExpenses($accountId, $startDate, $endDate);
        foreach ($expenses as $expense) {
            $payments[] = [
                'id' => $expense['id'],
                'type' => 'expense',
                'type_label' => 'Expense',
                'date' => $expense['expense_date'],
                'amount' => $expense['amount'],
                'description' => 'Category: ' . $expense['category_name'] . ' - ' . $expense['description'],
                'reference' => $expense['reference_number'] ?? 'N/A',
            ];
        }
        
        // Get agent payments
        $agentPayments = $this->getUnmatchedAgentPayments($accountId, $startDate, $endDate);
        foreach ($agentPayments as $payment) {
            $payments[] = [
                'id' => $payment['id'],
                'type' => 'agent_payment',
                'type_label' => 'Agent Commission',
                'date' => $payment['payment_date'],
                'amount' => $payment['amount'],
                'description' => 'Agent: ' . $payment['agent_name'],
                'reference' => $payment['payment_number'],
            ];
        }
        
        // Sort by date descending
        usort($payments, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $payments;
    }

    /**
     * Get unmatched vendor payments
     */
    protected function getUnmatchedVendorPayments($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('payments')
                      ->select('payments.*, bills.bill_number, vendors.name as vendor_name')
                      ->join('bills', 'bills.id = payments.bill_id')
                      ->join('vendors', 'vendors.id = payments.vendor_id')
                      ->where('payments.bank_account_id', $accountId)
                      ->where('payments.bank_transaction_id IS NULL');
        
        if ($startDate) {
            $builder->where('payments.payment_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('payments.payment_date <=', $endDate);
        }
        
        return $builder->orderBy('payments.payment_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get unmatched expenses
     */
    protected function getUnmatchedExpenses($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('expenses')
                      ->select('expenses.*, expense_categories.category_name')
                      ->join('expense_categories', 'expense_categories.id = expenses.category_id', 'left')
                      ->where('expenses.bank_account_id', $accountId)
                      ->where('expenses.bank_transaction_id IS NULL');
        
        if ($startDate) {
            $builder->where('expenses.expense_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('expenses.expense_date <=', $endDate);
        }
        
        return $builder->orderBy('expenses.expense_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get unmatched agent payments
     */
    protected function getUnmatchedAgentPayments($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('agent_payments')
                      ->select('agent_payments.*, agents.agent_name as agent_name')
                      ->join('agents', 'agents.id = agent_payments.agent_id')
                      ->where('agent_payments.bank_account_id', $accountId)
                      ->where('agent_payments.bank_transaction_id IS NULL');
        
        if ($startDate) {
            $builder->where('agent_payments.payment_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('agent_payments.payment_date <=', $endDate);
        }
        
        return $builder->orderBy('agent_payments.payment_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Manually match a bank transaction to a payment
     */
    public function matchTransaction($transactionId, $paymentType, $paymentId)
    {
        $db = \Config\Database::connect();
        
        // Start transaction
        $db->transStart();
        
        // Update bank transaction
        $db->table('bank_transactions')->where('id', $transactionId)->update([
            'is_reconciled' => 1,
            'reference_type' => $paymentType,
            'reference_id' => $paymentId
        ]);
        
        // Update payment record
        $paymentTable = $this->getPaymentTable($paymentType);
        $db->table($paymentTable)->where('id', $paymentId)->update([
            'bank_transaction_id' => $transactionId
        ]);
        
        // Complete transaction
        $db->transComplete();
        
        return $db->transStatus();
    }

    /**
     * Unmatch a bank transaction from a payment
     */
    public function unmatchTransaction($transactionId)
    {
        $db = \Config\Database::connect();
        
        // Get transaction details
        $transaction = $db->table('bank_transactions')
                         ->where('id', $transactionId)
                         ->get()
                         ->getRowArray();
        
        if (!$transaction || !$transaction['reference_type'] || !$transaction['reference_id']) {
            return false;
        }
        
        // Start transaction
        $db->transStart();
        
        // Update bank transaction
        $db->table('bank_transactions')->where('id', $transactionId)->update([
            'is_reconciled' => 0,
            'reference_type' => null,
            'reference_id' => null
        ]);
        
        // Update payment record
        $paymentTable = $this->getPaymentTable($transaction['reference_type']);
        $db->table($paymentTable)->where('id', $transaction['reference_id'])->update([
            'bank_transaction_id' => null
        ]);
        
        // Complete transaction
        $db->transComplete();
        
        return $db->transStatus();
    }

    /**
     * Get payment table name from type
     */
    protected function getPaymentTable($paymentType)
    {
        $tables = [
            'invoice_payment' => 'invoice_payments',
            'payment' => 'payments',
            'expense' => 'expenses',
            'agent_payment' => 'agent_payments',
        ];
        
        return $tables[$paymentType] ?? 'payments';
    }

    /**
     * Get reconciliation summary
     */
    public function getReconciliationSummary($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        
        // Count unmatched credits
        $unmatchedCredits = count($this->getUnmatchedCreditTransactions($accountId, $startDate, $endDate));
        
        // Count unmatched debits
        $unmatchedDebits = count($this->getUnmatchedDebitTransactions($accountId, $startDate, $endDate));
        
        // Count unmatched invoice payments
        $unmatchedInvoicePayments = count($this->getUnmatchedInvoicePayments($accountId, $startDate, $endDate));
        
        // Count unmatched outgoing payments
        $unmatchedOutgoingPayments = count($this->getAllUnmatchedOutgoingPayments($accountId, $startDate, $endDate));
        
        // Count matched transactions
        $builder = $db->table('bank_transactions')
                     ->where('bank_account_id', $accountId)
                     ->where('is_reconciled', 1);
        
        if ($startDate) {
            $builder->where('transaction_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('transaction_date <=', $endDate);
        }
        
        $matchedCount = $builder->countAllResults();
        
        return [
            'unmatched_credits' => $unmatchedCredits,
            'unmatched_debits' => $unmatchedDebits,
            'unmatched_invoice_payments' => $unmatchedInvoicePayments,
            'unmatched_outgoing_payments' => $unmatchedOutgoingPayments,
            'matched_transactions' => $matchedCount,
        ];
    }

    /**
     * Get matched transactions for reporting
     */
    public function getMatchedTransactions($accountId, $startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('bank_transactions')
                      ->where('bank_account_id', $accountId)
                      ->where('is_reconciled', 1);
        
        if ($startDate) {
            $builder->where('transaction_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('transaction_date <=', $endDate);
        }
        
        return $builder->orderBy('transaction_date', 'DESC')
                       ->get()
                       ->getResultArray();
    }
}
