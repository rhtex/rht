<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderItemModel extends Model
{
    protected $table            = 'sales_order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sales_order_id', 'product_id', 'description', 'hsn_code', 'quantity', 'rate',
        'tax_percentage', 'amount'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items by sales order ID with product details
     */
    public function getItemsByOrder($orderId)
    {
        return $this->select('sales_order_items.*, products.product_name, products.barcode, products.hsn_code as product_hsn')
            ->join('products', 'products.id = sales_order_items.product_id', 'left')
            ->where('sales_order_items.sales_order_id', $orderId)
            ->findAll();
    }
}
