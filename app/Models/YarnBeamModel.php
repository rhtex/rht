<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnBeamModel extends Model
{
    protected $table            = 'production_beams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['beam_number', 'status', 'condition_status', 'damaged_date', 'location', 'current_holder', 'remarks'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
