<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationItemModel extends Model
{
    protected $table            = 'quotation_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quotation_id', 'product_id', 'description', 'hsn_code', 'quantity', 'rate',
        'tax_percentage', 'amount'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items by quotation ID with product details
     */
    public function getItemsByQuotation($quotationId)
    {
        return $this->select('quotation_items.*, products.product_name, products.barcode, products.hsn_code as product_hsn')
            ->join('products', 'products.id = quotation_items.product_id', 'left')
            ->where('quotation_items.quotation_id', $quotationId)
            ->findAll();
    }
}
