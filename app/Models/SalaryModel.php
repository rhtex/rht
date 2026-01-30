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

    // Validation
    protected $validationRules      = [
        'employee_id'  => 'required|integer',
        'salary_month' => 'required|valid_date',
        'basic_salary' => 'required|numeric',
        'net_salary'   => 'required|numeric',
    ];

    /**
     * Get salaries with filters
     */
    public function getSalariesWithFilters($filters = [])
    {
        $builder = $this->select('salaries.*, employees.first_name, employees.last_name')
                        ->join('employees', 'employees.id = salaries.employee_id');

        if (!empty($filters['employee_id'])) {
            $builder->where('salaries.employee_id', $filters['employee_id']);
        }

        if (!empty($filters['month'])) {
            $builder->where('salaries.salary_month', $filters['month'] . '-01');
        }

        if (isset($filters['is_paid']) && $filters['is_paid'] !== '') {
            $builder->where('salaries.is_paid', $filters['is_paid']);
        }

        return $builder->orderBy('salaries.salary_month', 'DESC')
                        ->orderBy('employees.first_name', 'ASC')
                        ->findAll();
    }
}
