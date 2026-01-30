<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table            = 'loans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['employee_id', 'loan_amount', 'loan_date', 'processed_date', 'credited_date', 'credit_status', 'monthly_deduction', 'remaining_amount', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'employee_id'       => 'required|integer',
        'loan_amount'       => 'required|numeric',
        'loan_date'         => 'required|valid_date',
        'monthly_deduction' => 'required|numeric',
        'status'            => 'required|in_list[Pending,Approved,Rejected,Completed]',
    ];

    /**
     * Get loans with filters
     */
    public function getLoansWithFilters($filters = [])
    {
        $builder = $this->select('loans.*, employees.first_name, employees.last_name')
                        ->join('employees', 'employees.id = loans.employee_id');

        if (!empty($filters['employee_id'])) {
            $builder->where('loans.employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('loans.status', $filters['status']);
        }

        if (!empty($filters['credit_status'])) {
            $builder->where('loans.credit_status', $filters['credit_status']);
        }

        return $builder->orderBy('loans.created_at', 'DESC')->findAll();
    }
}
