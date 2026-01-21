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
}
