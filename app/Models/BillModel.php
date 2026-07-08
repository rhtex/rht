<?php

namespace App\Models;

use CodeIgniter\Model;

class BillModel extends Model
{
    protected $table = 'bills';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'vendor_id',
        'bill_number',
        'bill_date',
        'due_date',
        'reference_number',
        'status',
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
        'paid_amount',
        'balance',
        'notes',
        'terms',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'vendor_id' => 'required|integer',
        'bill_date' => 'required|valid_date',
        'due_date' => 'required|valid_date',
    ];

    /**
     * Get bills with vendor details
     */
    public function getBillsWithVendor($filters = [])
    {
        $builder = $this->select('bills.*, vendors.name as vendor_name')
            ->join('vendors', 'vendors.id = bills.vendor_id', 'left');

        if (!empty($filters['vendor_id'])) {
            $builder->where('bills.vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('bills.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('bills.bill_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('bills.bill_date <=', $filters['date_to']);
        }

        return $builder->orderBy('bills.created_at', 'DESC')->findAll();
    }

    /**
     * Get bill by ID with items and vendor
     */
    public function getBillById($id)
    {
        $bill = $this->select('bills.*, vendors.name as vendor_name, states.id as vendor_state_id, states.name as vendor_state')
            ->join('vendors', 'vendors.id = bills.vendor_id', 'left')
            ->join('addresses', 'addresses.owner_id = vendors.id AND addresses.owner_type = "vendor" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->find($id);

        if ($bill) {
            $itemModel = new \App\Models\BillItemModel();
            $bill['items'] = $itemModel->where('bill_id', $id)->findAll();

            $paymentModel = new \App\Models\PaymentModel();
            $bill['payments'] = $paymentModel->where('bill_id', $id)->orderBy('payment_date', 'DESC')->findAll();
        }

        return $bill;
    }

    /**
     * Update bill balance after payment
     */
    public function updateBalance($billId)
    {
        $bill = $this->find($billId);
        if (!$bill)
            return false;

        $balance = $bill['total_amount'] - $bill['paid_amount'];

        $status = 'Open';
        if ($balance <= 0) {
            $status = 'Paid';
        } elseif ($bill['paid_amount'] > 0) {
            $status = 'Partially Paid';
        } elseif (strtotime($bill['due_date']) < time() && $balance > 0) {
            $status = 'Overdue';
        }

        return $this->update($billId, [
            'balance' => $balance,
            'status' => $status
        ]);
    }

    /**
     * Get overdue bills
     */
    public function getOverdueBills()
    {
        return $this->where('due_date <', date('Y-m-d'))
            ->whereIn('status', ['Open', 'Partially Paid'])
            ->findAll();
    }

    /**
     * Calculate GST based on vendor state
     */
    public function calculateGST($billId, $vendorStateId, $companyStateId)
    {
        $itemModel = new \App\Models\BillItemModel();
        $items = $itemModel->where('bill_id', $billId)->findAll();

        // Get current bill data for discount, shipping, roundoff
        $currentBill = $this->find($billId);
        $discountAmount = $currentBill['discount_amount'] ?? 0;
        $discountType = $currentBill['discount_type'] ?? 'Amount';
        $shippingCharge = $currentBill['shipping_charge'] ?? 0;
        $roundoffAmount = $currentBill['roundoff_amount'] ?? 0;

        // 1. Calculate Gross Subtotal
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['rate']);
        }

        // 2. Calculate Total Discount
        $totalDiscount = 0;
        if ($discountType == 'Percentage') {
            $totalDiscount = ($subtotal * $discountAmount) / 100;
        } else {
            $totalDiscount = $discountAmount;
        }

        // 3. Calculate Taxable Amounts and Taxes
        $cgst = 0;
        $sgst = 0;
        $igst = 0;

        // Prioritize the is_inter_state flag if it's explicitly set on the bill
        $isInterState = isset($currentBill['is_inter_state']) ? (bool) $currentBill['is_inter_state'] : ($vendorStateId != $companyStateId);

        foreach ($items as $item) {
            $itemAmount = $item['quantity'] * $item['rate'];

            // Apportion discount to this item proportionally
            $itemDiscount = ($subtotal > 0) ? ($itemAmount / $subtotal * $totalDiscount) : 0;
            $taxableValue = $itemAmount - $itemDiscount;

            $taxAmount = ($taxableValue * $item['tax_percentage']) / 100;

            if ($isInterState) {
                $igst += $taxAmount;
            } else {
                $cgst += $taxAmount / 2;
                $sgst += $taxAmount / 2;
            }
        }

        $totalTax = $cgst + $sgst + $igst;

        // Total = (Subtotal - TotalDiscount) + Tax + Shipping + Roundoff
        $total = ($subtotal - $totalDiscount) + $totalTax + $shippingCharge + $roundoffAmount;

        $this->update($billId, [
            'subtotal' => $subtotal,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => $igst,
            'tax_amount' => $totalTax,
            'total_amount' => $total,
            'balance' => $total - ($currentBill['paid_amount'] ?? 0)
        ]);

        return true;
    }

    /**
     * Generate next bill number
     */
    public function generateBillNumber()
    {
        $prefix = 'BILL-' . date('Ym') . '-';
        $lastBill = $this->like('bill_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastBill) {
            $lastNumber = (int) substr($lastBill['bill_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
