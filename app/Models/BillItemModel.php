<?php

namespace App\Models;

use CodeIgniter\Model;

class BillItemModel extends Model
{
    protected $table            = 'bill_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_id', 'product_id', 'description', 'hsn_code', 'quantity', 'rate',
        'tax_percentage', 'cgst_rate', 'sgst_rate', 'igst_rate', 'amount'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items by bill ID
     */
    public function getItemsByBill($billId)
    {
        return $this->where('bill_id', $billId)->findAll();
    }

    /**
     * Apply GST rates to item based on inter-state flag
     */
    public function applyGSTRates(&$item, $isInterState)
    {
        if ($isInterState) {
            // Inter-state: IGST only
            $item['igst_rate'] = $item['tax_percentage'];
            $item['cgst_rate'] = 0;
            $item['sgst_rate'] = 0;
        } else {
            // Intra-state: CGST + SGST
            $item['cgst_rate'] = $item['tax_percentage'] / 2;
            $item['sgst_rate'] = $item['tax_percentage'] / 2;
            $item['igst_rate'] = 0;
        }

        // Calculate item amount
        $item['amount'] = $item['quantity'] * $item['rate'];

        return $item;
    }

    /**
     * Calculate bill total from items
     */
    public function calculateBillTotal($billId)
    {
        $items = $this->where('bill_id', $billId)->findAll();

        $subtotal = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            $itemAmount = $item['quantity'] * $item['rate'];
            $subtotal += $itemAmount;

            $taxAmount = ($itemAmount * $item['tax_percentage']) / 100;
            $totalTax += $taxAmount;
        }

        return [
            'subtotal' => $subtotal,
            'tax'      => $totalTax,
            'total'    => $subtotal + $totalTax
        ];
    }
}
