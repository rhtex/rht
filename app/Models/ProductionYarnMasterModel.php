<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionYarnMasterModel extends Model
{
    protected $table            = 'production_yarn_master';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'yarn_type', 'yarn_count'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name'       => 'required|min_length[3]|max_length[255]',
        'yarn_type'  => 'required|in_list[Dyed,Raw]',
        'yarn_count' => 'required|max_length[50]',
    ];
}
