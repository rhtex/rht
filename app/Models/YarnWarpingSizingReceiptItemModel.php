<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnWarpingSizingReceiptItemModel extends Model
{
    protected $table            = 'production_yarn_warping_sizing_receipt_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['receipt_id', 'dc_item_id', 'quantity_received_kg', 'quantity_wastage_kg', 'job_work_charges'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'receipt_id'           => 'required|integer',
        'dc_item_id'           => 'required|integer',
        'quantity_received_kg' => 'required|numeric|greater_than_equal_to[0]',
        'quantity_wastage_kg'  => 'permit_empty|numeric|greater_than_equal_to[0]',
    ];
}
