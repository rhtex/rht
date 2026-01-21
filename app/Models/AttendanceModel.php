<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['employee_id', 'attendance_date', 'check_in_time', 'check_out_time', 'status', 'hours_worked', 'shortfall_hours', 'surplus_hours', 'is_recovered', 'multiplier', 'remarks'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'employee_id'     => 'required|integer',
        'attendance_date' => 'required|valid_date',
        'status'          => 'required|in_list[Present,Absent,Half Day,Holiday]',
    ];
}
