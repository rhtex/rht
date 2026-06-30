<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnWarpingSizingReceiptModel extends Model
{
    protected $table            = 'production_yarn_warping_sizing_receipts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['dc_id', 'receipt_number', 'receipt_date', 'transport_charges', 'loading_charges', 'packing_charges', 'other_expenses', 'remarks', 'created_by', 'updated_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'dc_id'          => 'required|integer',
        'receipt_number' => 'required|is_unique[production_yarn_warping_sizing_receipts.receipt_number,id,{id}]',
        'receipt_date'   => 'required|valid_date[Y-m-d]',
    ];
}
