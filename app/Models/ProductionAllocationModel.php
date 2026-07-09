<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionAllocationModel extends Model
{
    protected $table            = 'production_allocations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'allocation_number', 'allocation_date', 'weaver_id', 'loom_id', 
        'warp_beam_id', 'contract_type', 'expected_return_date', 
        'status', 'reference_document_type', 'reference_document_id', 
        'remarks', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
