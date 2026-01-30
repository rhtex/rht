<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BankAccountModel;
use App\Models\ReconciliationModel;
use App\Models\BankTransactionModel;

class ReconciliationController extends BaseController
{
    protected $session;
    protected $bankAccountModel;
    protected $reconciliationModel;
    protected $bankTransactionModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->bankAccountModel = new BankAccountModel();
        $this->reconciliationModel = new ReconciliationModel();
        $this->bankTransactionModel = new BankTransactionModel();
    }

    /**
     * Reconciliation dashboard - shows all accounts
     */
    public function index()
    {
        // Get all bank accounts (not cash)
        $accounts = $this->bankAccountModel->getBankAccounts();
        
        // Get reconciliation summary for each account
        foreach ($accounts as &$account) {
            $account['summary'] = $this->reconciliationModel->getReconciliationSummary($account['id']);
        }
        
        $data = [
            'title' => 'Bank Reconciliation',
            'accounts' => $accounts
        ];
        
        return view('reconciliation/index', $data);
    }

    /**
     * Show reconciliation page for specific account
     */
    public function account($accountId)
    {
        $account = $this->bankAccountModel->find($accountId);
        
        if (!$account) {
            $this->session->setFlashdata('error', 'Bank account not found');
            return redirect()->to(base_url('reconciliation'));
        }
        
        // Get date range from request or use current month
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        // Get unmatched credit transactions (money in)
        $unmatchedCredits = $this->reconciliationModel->getUnmatchedCreditTransactions($accountId, $startDate, $endDate);
        
        // Get unmatched debit transactions (money out)
        $unmatchedDebits = $this->reconciliationModel->getUnmatchedDebitTransactions($accountId, $startDate, $endDate);
        
        // Get unmatched invoice payments
        $unmatchedInvoicePayments = $this->reconciliationModel->getUnmatchedInvoicePayments($accountId, $startDate, $endDate);
        
        // Get all unmatched outgoing payments
        $unmatchedOutgoingPayments = $this->reconciliationModel->getAllUnmatchedOutgoingPayments($accountId, $startDate, $endDate);
        
        // Get matched transactions
        $matchedTransactions = $this->reconciliationModel->getMatchedTransactions($accountId, $startDate, $endDate);
        
        // Get summary
        $summary = $this->reconciliationModel->getReconciliationSummary($accountId, $startDate, $endDate);
        
        $data = [
            'title' => 'Reconcile - ' . $account['bank_name'],
            'account' => $account,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unmatched_credits' => $unmatchedCredits,
            'unmatched_debits' => $unmatchedDebits,
            'unmatched_invoice_payments' => $unmatchedInvoicePayments,
            'unmatched_outgoing_payments' => $unmatchedOutgoingPayments,
            'matched_transactions' => $matchedTransactions,
            'summary' => $summary
        ];
        
        return view('reconciliation/account', $data);
    }

    /**
     * Process manual matching (AJAX)
     */
    public function match()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $transactionId = $this->request->getPost('transaction_id');
        $paymentType = $this->request->getPost('payment_type');
        $paymentId = $this->request->getPost('payment_id');
        
        if (!$transactionId || !$paymentType || !$paymentId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
        }
        
        $result = $this->reconciliationModel->matchTransaction($transactionId, $paymentType, $paymentId);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaction matched successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to match transaction'
            ]);
        }
    }

    /**
     * Unmatch a transaction (AJAX)
     */
    public function unmatch()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $transactionId = $this->request->getPost('transaction_id');
        
        if (!$transactionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Transaction ID is required'
            ]);
        }
        
        $result = $this->reconciliationModel->unmatchTransaction($transactionId);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaction unmatched successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to unmatch transaction'
            ]);
        }
    }

    /**
     * Generate reconciliation report
     */
    public function report($accountId, $startDate, $endDate)
    {
        $account = $this->bankAccountModel->find($accountId);
        
        if (!$account) {
            $this->session->setFlashdata('error', 'Bank account not found');
            return redirect()->to(base_url('reconciliation'));
        }
        
        // Get matched transactions
        $matchedTransactions = $this->reconciliationModel->getMatchedTransactions($accountId, $startDate, $endDate);
        
        // Get unmatched transactions
        $unmatchedCredits = $this->reconciliationModel->getUnmatchedCreditTransactions($accountId, $startDate, $endDate);
        $unmatchedDebits = $this->reconciliationModel->getUnmatchedDebitTransactions($accountId, $startDate, $endDate);
        
        // Get unmatched payments
        $unmatchedInvoicePayments = $this->reconciliationModel->getUnmatchedInvoicePayments($accountId, $startDate, $endDate);
        $unmatchedOutgoingPayments = $this->reconciliationModel->getAllUnmatchedOutgoingPayments($accountId, $startDate, $endDate);
        
        // Calculate balances
        $openingBalance = 0; // You may want to get this from account history
        $totalCredits = array_sum(array_column($matchedTransactions, 'type') === 'credit' ? array_column($matchedTransactions, 'amount') : [0]);
        $totalDebits = array_sum(array_column($matchedTransactions, 'type') === 'debit' ? array_column($matchedTransactions, 'amount') : [0]);
        $closingBalance = $account['current_balance'];
        
        $data = [
            'title' => 'Reconciliation Report - ' . $account['bank_name'],
            'account' => $account,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'matched_transactions' => $matchedTransactions,
            'unmatched_credits' => $unmatchedCredits,
            'unmatched_debits' => $unmatchedDebits,
            'unmatched_invoice_payments' => $unmatchedInvoicePayments,
            'unmatched_outgoing_payments' => $unmatchedOutgoingPayments,
            'opening_balance' => $openingBalance,
            'total_credits' => $totalCredits,
            'total_debits' => $totalDebits,
            'closing_balance' => $closingBalance
        ];
        
        return view('reconciliation/report', $data);
    }
}
