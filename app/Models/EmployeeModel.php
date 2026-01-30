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
    ];

    /**
     * Get employees with filters
     */
    public function getEmployeesWithFilters($filters = [])
    {
        $builder = $this->select('employees.*, users.id as user_id, states.name as state_name, countries.name as country_name')
                        ->join('users', 'users.employee_id = employees.id', 'left')
                        ->join('states', 'states.id = employees.state_id', 'left')
                        ->join('countries', 'countries.id = employees.country_id', 'left')
                        ->where('employees.deleted_at', null);

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('first_name', $filters['search'])
                    ->orLike('last_name', $filters['search'])
                    ->orLike('mobile_number', $filters['search'])
                    ->orLike('email', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('employees.status', $filters['status']);
        }

        if (!empty($filters['employment_type'])) {
            $builder->where('employees.employment_type', $filters['employment_type']);
        }

        return $builder->orderBy('first_name', 'ASC')->findAll();
    }
}
