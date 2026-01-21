<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanPaymentModel extends Model
{
    protected $table            = 'loan_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['loan_id', 'salary_id', 'payment_date', 'amount_paid', 'remaining_after_payment', 'payment_month'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'loan_id'      => 'required|integer',
        'payment_date' => 'required|valid_date',
        'amount_paid'  => 'required|decimal',
    ];
}
