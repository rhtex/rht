<?php

namespace App\Models;

use CodeIgniter\Model;

class BankAccountModel extends Model
{
    protected $table            = 'bank_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['bank_name', 'account_number', 'ifsc_code', 'branch_name', 'account_type', 'current_balance', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'id' => 'permit_empty|integer',
        'bank_name'       => 'required|min_length[2]|max_length[100]',
        'account_number'  => 'required|min_length[5]|max_length[50]|is_unique[bank_accounts.account_number,id,{id}]',
        'ifsc_code'       => 'permit_empty',
        'branch_name'     => 'required|min_length[2]|max_length[100]',
        'account_type'    => 'required|in_list[Bank,Cash]',
        'current_balance' => 'permit_empty|numeric',
        'status'          => 'required|in_list[active,inactive]',
    ];

    protected $validationMessages   = [
        'ifsc_code' => [
            'required'     => 'IFSC code is required for Bank accounts.',
            'exact_length' => 'IFSC code must be exactly 11 characters.',
            'alpha_numeric'=> 'IFSC code must contain only letters and numbers.',
        ],
    ];

    /**
     * Dynamically clean / adjust validation rules based on row data
     */
    protected function cleanValidationRules(array $rules, array $row): array
    {
        $rules = parent::cleanValidationRules($rules, $row);

        if (isset($row['account_type']) && $row['account_type'] === 'Cash') {
            $rules['ifsc_code'] = 'permit_empty';
        } elseif (isset($row['account_type']) && $row['account_type'] === 'Bank') {
            $rules['ifsc_code'] = 'required|exact_length[11]|alpha_numeric';
        }

        return $rules;
    }

    /**
     * Get accounts with filters
     */
    public function getAccountsWithFilters($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('bank_name', $filters['search'])
                    ->orLike('account_number', $filters['search'])
                    ->orLike('branch_name', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['account_type'])) {
            $builder->where('account_type', $filters['account_type']);
        }

        return $builder->orderBy('bank_name', 'ASC')->get()->getResultArray();
    }

    /**
     * Get only cash accounts
     */
    public function getCashAccounts()
    {
        return $this->where('account_type', 'Cash')
                    ->where('status', 'active')
                    ->orderBy('bank_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get only bank accounts (not cash)
     */
    public function getBankAccounts()
    {
        return $this->where('account_type', 'Bank')
                    ->where('status', 'active')
                    ->orderBy('bank_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get default "Cash in Hand" account
     */
    public function getCashInHandAccount()
    {
        return $this->where('account_type', 'Cash')
                    ->where('account_number', 'CASH-001')
                    ->first();
    }
}

