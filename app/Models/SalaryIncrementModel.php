<?php

namespace App\Models;

use CodeIgniter\Model;

class SalaryIncrementModel extends Model
{
    protected $table            = 'salary_increments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['employee_id', 'old_salary', 'new_salary', 'effective_date', 'remarks'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules      = [
        'employee_id'    => 'required|integer',
        'old_salary'     => 'required|decimal',
        'new_salary'     => 'required|decimal',
        'effective_date' => 'required|valid_date',
    ];
}
