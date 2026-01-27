<?php

namespace App\Models;

use CodeIgniter\Model;

class TaxModel extends Model
{
    protected $table            = 'taxes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'percentage', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'       => 'required|min_length[3]|max_length[100]',
        'percentage' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'status'     => 'required|in_list[Active,Inactive]',
    ];
}
