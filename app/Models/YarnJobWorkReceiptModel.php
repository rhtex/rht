<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnJobWorkReceiptModel extends Model
{
    protected $table            = 'production_yarn_job_work_receipts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dc_id', 'receipt_number', 'receipt_date', 'transport_charges', 
        'loading_charges', 'packing_charges', 'other_expenses', 'remarks', 'created_by', 'created_at'
    ];

    protected $useTimestamps = false;

    protected $validationRules      = [
        'dc_id'          => 'required|integer',
        'receipt_number' => 'required|is_unique[production_yarn_job_work_receipts.receipt_number,id,{id}]',
        'receipt_date'   => 'required|valid_date',
    ];
}
