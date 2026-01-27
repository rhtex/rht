<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceItemModel extends Model
{
    protected $table            = 'invoice_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_id', 'product_id', 'description', 'hsn_code', 'quantity', 'rate',
        'tax_percentage', 'cgst_rate', 'sgst_rate', 'igst_rate', 'amount'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items by invoice ID
     */
    public function getItemsByInvoice($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)->findAll();
    }
}
