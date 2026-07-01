<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnWarpingSizingDcBeamModel extends Model
{
    protected $table            = 'production_yarn_warping_sizing_dc_beams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['dc_id', 'receipt_id', 'returned_status', 'meters', 'sizing_no', 'color', 'return_date', 'beam_number', 'remarks'];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'dc_id'       => 'required|integer',
        'beam_number' => 'required',
    ];
}
