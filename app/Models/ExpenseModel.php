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
        'payment_mode', 'bank_account_id', 'reference_number'
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
}
