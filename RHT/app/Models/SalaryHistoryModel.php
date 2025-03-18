<?php

namespace App\Models;

use CodeIgniter\Model;

class SalaryHistoryModel extends Model
{
    protected $table = 'salary_history';
    protected $primaryKey = 'salary_id';

    protected $allowedFields = [
        'employee_id', 
        'salary_amount', 
        'start_date', 
        'end_date', 
        'status', 
        'created_at', 
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
