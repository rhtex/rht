<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentModel extends Model
{
    protected $table            = 'agents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'agent_name', 'commission_percentage', 'phone_number', 'address_proof_type', 'address_proof_id',
        'address_proof_front', 'address_proof_back', 'address_1', 'address_2',
        'village', 'city', 'state_id', 'country_id', 'pincode'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'id'                 => 'permit_empty|integer',
        'agent_name'         => 'required|min_length[2]|max_length[100]',
        'phone_number'       => 'required|min_length[10]|max_length[20]',
        'city'                  => 'required|max_length[100]',
        'state_id'              => 'required|numeric',
        'country_id'            => 'required|numeric',
        'pincode'               => 'required|max_length[10]',
        'commission_percentage' => 'required|decimal'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
