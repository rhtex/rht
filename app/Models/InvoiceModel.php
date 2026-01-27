<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'customer_id', 'agent_id', 'agent_commission_percent', 'agent_commission_amount',
        'agent_commission_status', 'agent_commission_paid_at', 'invoice_number', 'zoho_invoice_id',
        'zoho_sync_status', 'invoice_date', 'due_date', 'reference_number', 'transport_name',
        'waybill_number', 'packages_count', 'waybill_date', 'ewaybill_number', 'status', 'subtotal', 'discount_amount', 'discount_type',
        'shipping_charge', 'cgst_amount', 'sgst_amount', 'igst_amount', 'tax_amount',
        'roundoff_amount', 'total_amount', 'paid_amount', 'balance', 'notes', 'terms',
        'created_by', 'updated_by', 'zoho_sync_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'customer_id'   => 'required|integer',
        'invoice_date'  => 'required|valid_date',
        'due_date'      => 'required|valid_date',
    ];

    /**
     * Get invoices with customer details
     */
    public function getInvoicesWithCustomer($filters = [])
    {
        $builder = $this->select('invoices.*, customers.name as customer_name, addresses.city as customer_city')
                        ->join('customers', 'customers.id = invoices.customer_id', 'left')
                        ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing"', 'left');

        if (!empty($filters['customer_id'])) {
            $builder->where('invoices.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('invoices.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('invoices.invoice_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('invoices.invoice_date <=', $filters['date_to']);
        }

        return $builder->orderBy('invoices.created_at', 'DESC')->findAll();
    }

    /**
     * Get invoice by ID with items and customer
     */
    public function getInvoiceById($id)
    {
        $invoice = $this->select('invoices.*, customers.name as customer_name, states.id as customer_state_id, states.name as customer_state')
                       ->join('customers', 'customers.id = invoices.customer_id', 'left')
                       ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing"', 'left')
                       ->join('states', 'states.id = addresses.state_id', 'left')
                       ->find($id);

        if ($invoice) {
            $itemModel = new \App\Models\InvoiceItemModel();
            $invoice['items'] = $itemModel->where('invoice_id', $id)->findAll();

            $paymentModel = new \App\Models\InvoicePaymentModel();
            $invoice['payments'] = $paymentModel->where('invoice_id', $id)->orderBy('payment_date', 'DESC')->findAll();
        }

        return $invoice;
    }

    /**
     * Update invoice balance after payment
     */
    public function updateBalance($invoiceId)
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) return false;

        $balance = $invoice['total_amount'] - $invoice['paid_amount'];

        $status = 'Open';
        if ($balance <= 0) {
            $status = 'Paid';
        } elseif ($invoice['paid_amount'] > 0) {
            $status = 'Partially Paid';
        } elseif (strtotime($invoice['due_date']) < time() && $balance > 0) {
            $status = 'Overdue';
        }

        return $this->update($invoiceId, [
            'balance' => $balance,
            'status'  => $status
        ]);
    }

    /**
     * Calculate GST based on customer state
     */
    public function calculateGST($invoiceId, $customerStateId, $companyStateId)
    {
        $itemModel = new \App\Models\InvoiceItemModel();
        $items = $itemModel->where('invoice_id', $invoiceId)->findAll();

        $currentInvoice = $this->find($invoiceId);
        $discountAmount = $currentInvoice['discount_amount'] ?? 0;
        $discountType = $currentInvoice['discount_type'] ?? 'Fixed';
        $shippingCharge = $currentInvoice['shipping_charge'] ?? 0;
        $roundoffAmount = $currentInvoice['roundoff_amount'] ?? 0;

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['rate']);
        }

        $totalDiscount = 0;
        if ($discountType == 'Percentage') {
            $totalDiscount = ($subtotal * $discountAmount) / 100;
        } else {
            $totalDiscount = $discountAmount;
        }

        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        $isInterState = ($customerStateId != $companyStateId);

        foreach ($items as $item) {
            $itemAmount = $item['quantity'] * $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmount / $subtotal * $totalDiscount) : 0;
            $taxableValue = $itemAmount - $itemDiscount;
            
            $taxAmount = ($taxableValue * $item['tax_percentage']) / 100;

            if ($isInterState) {
                $igst += $taxAmount;
                $itemModel->update($item['id'], ['igst_rate' => $item['tax_percentage'], 'cgst_rate' => 0, 'sgst_rate' => 0]);
            } else {
                $cgst += $taxAmount / 2;
                $sgst += $taxAmount / 2;
                $itemModel->update($item['id'], ['cgst_rate' => $item['tax_percentage'] / 2, 'sgst_rate' => $item['tax_percentage'] / 2, 'igst_rate' => 0]);
            }
        }

        $totalTax = $cgst + $sgst + $igst;
        $total = ($subtotal - $totalDiscount) + $totalTax + $shippingCharge + $roundoffAmount;

        $this->update($invoiceId, [
            'subtotal'     => $subtotal,
            'cgst_amount'  => $cgst,
            'sgst_amount'  => $sgst,
            'igst_amount'  => $igst,
            'tax_amount'   => $totalTax,
            'total_amount' => $total,
            'balance'      => $total - ($currentInvoice['paid_amount'] ?? 0)
        ]);

        return true;
    }

    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $lastInvoice = $this->like('invoice_number', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice['invoice_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
