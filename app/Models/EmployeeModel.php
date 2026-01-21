<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'first_name', 'last_name', 'guardian_first_name', 'guardian_last_name', 'guardian_type',
        'mobile_number', 'guardian_mobile_number', 'email', 'photo',
        'joining_date', 'inactive_date',
        'address_line_1', 'address_line_2', 'village', 'city', 'state', 'country', 'state_id', 'country_id', 'pincode',
        'address_proof_type', 'address_proof_number', 'address_proof_front_image', 'address_proof_back_image',
        'status', 'basic_salary', 'daily_working_hours', 'employment_type'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id'            => 'permit_empty|integer',
        'first_name'    => 'required|min_length[2]|max_length[100]',
        'last_name'     => 'required|min_length[2]|max_length[100]',
        'mobile_number' => 'required|numeric|min_length[10]|max_length[15]',
        'email'         => 'permit_empty|valid_email|is_unique[employees.email,id,{id}]',
        'joining_date'  => 'required|valid_date',
        'status'        => 'required|in_list[active,inactive]',
        'basic_salary'  => 'required|numeric',
        'daily_working_hours' => 'required|numeric',
        'employment_type' => 'required|in_list[Permanent,Temporary]',
        // Images (handled in Controller/Service, but rules here for basics)
        // 'photo' => 'uploaded[photo]|max_size[photo,2048]|is_image[photo]' // Validating file uploads in model can be tricky if optional on edit
    ];
}
