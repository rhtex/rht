<?php

namespace App\Models;

use CodeIgniter\Model;

class BankTransactionModel extends Model
{
    protected $table            = 'bank_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['bank_account_id', 'transaction_date', 'description', 'type', 'amount', 'reference_number', 'balance_after', 'is_reconciled', 'reference_type', 'reference_id'];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'bank_account_id'  => 'required|is_not_unique[bank_accounts.id]',
        'transaction_date' => 'required|valid_date',
        'type'             => 'required|in_list[credit,debit]',
        'amount'           => 'required|decimal|greater_than[0]',
        'description'      => 'required|min_length[3]|max_length[255]',
    ];

    public function getTransactionsWithFilters($accountId, $filters = [])
    {
        $builder = $this->where('bank_account_id', $accountId);

        if (!empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('transaction_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('transaction_date <=', $filters['date_to']);
        }

        return $builder->orderBy('transaction_date', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->findAll();
    }

    public function getTransactionsByAccount($accountId)
    {
        return $this->where('bank_account_id', $accountId)
                    ->orderBy('transaction_date', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Get unreconciled transactions for an account
     */
    public function getUnreconciledTransactions($accountId, $type = null)
    {
        $builder = $this->where('bank_account_id', $accountId)
                        ->where('is_reconciled', 0);
        
        if ($type) {
            $builder->where('type', $type);
        }
        
        return $builder->orderBy('transaction_date', 'DESC')
                       ->orderBy('id', 'DESC')
                       ->findAll();
    }

    /**
     * Get transactions by reference
     */
    public function getTransactionsByReference($referenceType, $referenceId)
    {
        return $this->where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->findAll();
    }

    /**
     * Mark transaction as reconciled
     */
    public function markAsReconciled($transactionId, $referenceType = null, $referenceId = null)
    {
        $data = ['is_reconciled' => 1];
        
        if ($referenceType && $referenceId) {
            $data['reference_type'] = $referenceType;
            $data['reference_id'] = $referenceId;
        }
        
        return $this->update($transactionId, $data);
    }

    /**
     * Unmark transaction (remove reconciliation)
     */
    public function unmarkReconciled($transactionId)
    {
        return $this->update($transactionId, [
            'is_reconciled' => 0,
            'reference_type' => null,
            'reference_id' => null
        ]);
    }
}
