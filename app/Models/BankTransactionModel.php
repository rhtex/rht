<?php

namespace App\Models;

use CodeIgniter\Model;

class BankTransactionModel extends Model
{
    protected $table            = 'bank_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['bank_account_id', 'transaction_date', 'description', 'type', 'amount', 'reference_number', 'balance_after'];

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

    public function getTransactionsByAccount($accountId)
    {
        return $this->where('bank_account_id', $accountId)
                    ->orderBy('transaction_date', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
