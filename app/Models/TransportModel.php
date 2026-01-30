<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportModel extends Model
{
    protected $table            = 'transports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'transport_name', 'transport_code', 'branch', 'branch_address',
        'branch_phone_number', 'branch_gst_number', 'state_id', 'country_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'id'                  => 'permit_empty|integer',
        'transport_name'      => 'required|min_length[2]|max_length[100]',
        'branch'              => 'required',
        'branch_phone_number' => 'required',
        'branch_address'      => 'required',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get transports with filters
     */
    public function getTransportsWithFilters($filters = [])
    {
        $builder = $this->select('transports.*, states.name as state_name, countries.name as country_name')
                        ->join('states', 'states.id = transports.state_id', 'left')
                        ->join('countries', 'countries.id = transports.country_id', 'left');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('transport_name', $filters['search'])
                    ->orLike('transport_code', $filters['search'])
                    ->orLike('branch_phone_number', $filters['search'])
                    ->orLike('branch', $filters['search'])
                    ->groupEnd();
        }

        return $builder->orderBy('transport_name', 'ASC')->findAll();
    }
}
