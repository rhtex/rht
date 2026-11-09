<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderItemModel extends Model
{
    protected $table = 'purchase_order_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'purchase_order_id',
        'product_id',
        'description',
        'hsn_code',
        'quantity',
        'rate',
        'tax_percentage',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'amount',
    ];
}

