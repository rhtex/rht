<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeePaymentsModel extends Model
{
    protected $table = 'employee_payments';  // Name of the table
    protected $primaryKey = 'id';  // Primary key field
    protected $useAutoIncrement = true;  // Enable auto incrementing for primary key
    protected $allowedFields = [
        'employee_id', 
        'salary_amount', 
        'payment_date', 
        'month', 
        'payslip_date', 
        'payment_id', 
        'payment_type', 
        'year', 
        'monthly_salary', 
        'present_days', 
        'salary_days_count', 
        'per_day_salary'
    ];  // Columns that can be inserted/updated
    protected $useTimestamps = true;  // Automatically manage created_at and updated_at timestamps (optional)
    protected $createdField = 'created_at';  // Custom timestamp column (optional)
    protected $updatedField = 'updated_at';  // Custom timestamp column (optional)
    
    // Optional: Define validation rules for data
    protected $validationRules = [
        'employee_id' => 'required|integer',
        'salary_amount' => 'required|decimal',
        'payment_date' => 'valid_date',
        'month' => 'required|exact_length[7]',  // Format: YYYY-MM
        'payslip_date' => 'required|valid_date',
        'payment_id' => 'string',  // Example: Payment ID must be a string
        'payment_type' => 'string',  // Example: Payment type must be a string
        'year' => 'required|integer',  // Ensure the year is an integer
        'monthly_salary' => 'required|decimal',
        'present_days' => 'required|integer',
        'salary_days_count' => 'required|integer',
        'per_day_salary' => 'required|decimal',
    ];
    
    protected $validationMessages = [
        'employee_id' => [
            'required' => 'Employee ID is required',
            'integer' => 'Employee ID must be an integer',
        ],
        'salary_amount' => [
            'required' => 'Salary amount is required',
            'decimal' => 'Salary amount must be a valid decimal number',
        ],
        'payment_date' => [
            'valid_date' => 'Valid Date must be there',
        ],
        'month' => [
            'required' => 'Month is required',
            'exact_length' => 'Month must be in the format YYYY-MM',
        ],
        'payslip_date' => [
            'valid_date' => 'Payslip date must be a valid date',
        ],
        'payment_id' => [
            'string' => 'Payment ID must be a string',
        ],
        'payment_type' => [
            'string' => 'Payment type must be a string',
        ],
        'year' => [
            'required' => 'Year is required',
            'integer' => 'Year must be an integer',
        ],
        'monthly_salary' => [
            'required' => 'Monthly salary is required',
            'decimal' => 'Monthly salary must be a valid decimal number',
        ],
        'present_days' => [
            'required' => 'Present days is required',
            'integer' => 'Present days must be an integer',
        ],
        'salary_days_count' => [
            'required' => 'Salary days count is required',
            'integer' => 'Salary days count must be an integer',
        ],
        'per_day_salary' => [
            'required' => 'Per day salary is required',
            'decimal' => 'Per day salary must be a valid decimal number',
        ],
    ];

    // Optionally, you can add custom methods for fetching specific data or calculating values
    public function getSalaryHistory($employeeId, $month)
    {
        return $this->where('employee_id', $employeeId)
                    ->where('month', $month)
                    ->first();  // Return the first record (should only be one salary entry per month)
    }

    // Method to get all payments for a specific employee
    public function getAllPayments($employeeId)
    {
        return $this->where('employee_id', $employeeId)
                    ->findAll();
    }
}
