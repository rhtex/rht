<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionAllocationWeftModel extends Model
{
    protected $table            = 'production_allocation_wefts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'allocation_id', 'yarn_id', 'color_id', 'batch_number', 
        'issued_weight', 'rate'
    ];
}
