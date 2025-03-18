<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'first_name', 'last_name', 'guardian_name', 'guardian_type', 'guardian_mobile', 'employee_mobile', 
        'email', 'address', 'address_proof_no', 'address_proof_type', 'address_proof_upload', 
        'employee_photo_upload', 'designation', 'inactive_date', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // Validation rules can be added if necessary
}
?>