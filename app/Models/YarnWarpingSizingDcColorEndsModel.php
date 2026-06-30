<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnWarpingSizingDcColorEndsModel extends Model
{
    protected $table            = 'production_yarn_warping_sizing_dc_color_ends';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['dc_id', 'color', 'ends_count'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'dc_id' => 'required|integer',
        'color' => 'required',
    ];
}
