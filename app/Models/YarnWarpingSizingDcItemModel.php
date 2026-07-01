<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnWarpingSizingDcItemModel extends Model
{
    protected $table            = 'production_yarn_warping_sizing_dc_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['dc_id', 'mill_name', 'yarn_count', 'warp_weft', 'warp_yarn_type', 'csp', 'lot_number', 'yarn_type', 'current_color', 'quantity_issued_kg', 'quantity_received_kg', 'quantity_wastage_kg'];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'dc_id'              => 'required|integer',
        'mill_name'          => 'required|min_length[3]|max_length[255]',
        'yarn_count'         => 'required|max_length[50]',
        'warp_weft'          => 'required|in_list[Warp,Weft]',
        'yarn_type'          => 'required|in_list[Raw,Dyed]',
        'quantity_issued_kg' => 'required|numeric|greater_than[0]',
    ];
}
