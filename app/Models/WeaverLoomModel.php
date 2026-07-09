<?php

namespace App\Models;

use CodeIgniter\Model;

class WeaverLoomModel extends Model
{
    protected $table            = 'weaver_looms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'weaver_id', 'loom_number', 'contract_type', 'status',
        'loom_owner', 'loom_cost', 'jacquard_owner', 'jacquard_cost',
        'fitted_by', 'fitting_cost',
        'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'weaver_id'     => 'required|integer',
        'loom_number'   => 'required|min_length[1]|max_length[100]',
        'contract_type' => 'required|in_list[Job Work,Sale & Buy Back]',
        'status'        => 'in_list[Active,Inactive]',
    ];
}
