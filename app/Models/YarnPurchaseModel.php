<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnPurchaseModel extends Model
{
    protected $table            = 'production_yarn_purchases';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'purchase_date', 'supplier', 'invoice_number', 'mill_name', 'yarn_count', 'material_type', 'warp_weft', 
        'csp', 'lot_number', 'number_bags', 'number_cones', 'total_weight_kg', 'rate_per_kg', 'gst_percent', 
        'transport_charges', 'other_charges', 'warehouse_location', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'purchase_date'     => 'required|valid_date',
        'supplier'          => 'required|min_length[3]|max_length[255]',
        'invoice_number'    => 'required|max_length[100]',
        'mill_name'         => 'required|min_length[3]|max_length[255]',
        'yarn_count'        => 'required|max_length[50]',
        'material_type'     => 'required|max_length[100]',
        'warp_weft'         => 'required|in_list[Warp,Weft]',
        'total_weight_kg'   => 'required|numeric|greater_than[0]',
        'rate_per_kg'       => 'required|numeric|greater_than_equal_to[0]',
    ];
}
