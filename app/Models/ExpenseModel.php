<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table            = 'expenses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'expense_date', 'category_id', 'amount', 'description', 
        'payment_mode', 'bank_account_id', 'bank_transaction_id', 'reference_number'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'expense_date'    => 'required|valid_date',
        'category_id'     => 'required|integer|is_not_unique[expense_categories.id]',
        'amount'          => 'required|numeric',
        'payment_mode'    => 'required',
        'bank_account_id' => 'permit_empty|integer',
    ];
    /**
     * Get expenses with filters
     */
    public function getExpensesWithFilters($filters = [])
    {
        $builder = $this->select('expenses.*, bank_accounts.bank_name, bank_accounts.account_number, expense_categories.category_name')
                        ->join('bank_accounts', 'bank_accounts.id = expenses.bank_account_id', 'left')
                        ->join('expense_categories', 'expense_categories.id = expenses.category_id', 'left');

        if (!empty($filters['category_id'])) {
            $builder->where('expenses.category_id', $filters['category_id']);
        }

        if (!empty($filters['bank_account_id'])) {
            $builder->where('expenses.bank_account_id', $filters['bank_account_id']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('expenses.expense_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('expenses.expense_date <=', $filters['date_to']);
        }

        return $builder->orderBy('expenses.expense_date', 'DESC')->findAll();
    }
}
