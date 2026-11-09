<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'vendor_id',
        'purchase_order_number',
        'order_date',
        'delivery_date',
        'reference_number',
        'status',
        'converted_to_bill_id',
        'is_inter_state',
        'subtotal',
        'discount_amount',
        'discount_type',
        'shipping_charge',
        'roundoff_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'terms',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getOrderById($id)
    {
        $order = $this->select('purchase_orders.*, vendors.name as vendor_name, vendors.gstin as vendor_gstin')
            ->join('vendors', 'vendors.id = purchase_orders.vendor_id', 'left')
            ->find($id);

        if ($order) {
            $itemModel = new PurchaseOrderItemModel();
            $order['items'] = $itemModel->where('purchase_order_id', $id)->findAll();
        }

        return $order;
    }

    public function generateOrderNumber()
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $last = $this->like('purchase_order_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last['purchase_order_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}

