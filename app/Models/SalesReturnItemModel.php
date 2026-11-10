<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesReturnItemModel extends Model
{
    protected $table            = 'sales_return_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sales_return_id', 'product_id', 'description', 'quantity', 'rate',
        'tax_percentage', 'amount'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items by return ID with product details
     */
    public function getItemsByReturn($returnId)
    {
        return $this->select('sales_return_items.*, products.product_name, products.barcode, products.hsn_code as product_hsn')
            ->join('products', 'products.id = sales_return_items.product_id', 'left')
            ->where('sales_return_items.sales_return_id', $returnId)
            ->findAll();
    }
}
