<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionReceiptModel extends Model
{
    protected $table            = 'production_receipts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'receipt_number', 'receipt_date', 'weaver_id', 'allocation_id', 
        'status', 'reference_document_type', 'reference_document_id', 
        'remarks', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'receipt_number' => 'required|is_unique[production_receipts.receipt_number,id,{id}]',
        'receipt_date'   => 'required|valid_date',
        'weaver_id'      => 'required|integer',
        'status'         => 'in_list[Pending QC,Approved,Cancelled]',
    ];
}
