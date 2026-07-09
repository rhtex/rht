<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionReceiptItemModel extends Model
{
    protected $table            = 'production_receipt_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'receipt_id', 'item_type', 'product_id', 'quality_status', 
        'quantity', 'weight', 'rate_type', 'rate', 'wage_amount'
    ];
}
