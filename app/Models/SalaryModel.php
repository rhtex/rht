<?php

namespace App\Models;

use CodeIgniter\Model;

class SalaryModel extends Model
{
    protected $table            = 'salaries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['employee_id', 'salary_month', 'basic_salary', 'allowances', 'deductions', 'net_salary', 'is_paid', 'paid_date', 'exempt_loan_deduction'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'employee_id'  => 'required|integer',
        'salary_month' => 'required|valid_date', // Y-m-d format usually, or Y-m
        'basic_salary' => 'required|numeric',
        'net_salary'   => 'required|numeric',
    ];
}
