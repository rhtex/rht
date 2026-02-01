<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'agent_id',
        'agent_commission_percent',
        'agent_commission_amount',
        'agent_commission_status',
        'agent_commission_paid_at',
        'invoice_number',
        'zoho_invoice_id',
        'zoho_sync_status',
        'invoice_date',
        'due_date',
        'reference_number',
        'transport_name',
        'waybill_number',
        'packages_count',
        'waybill_date',
        'ewaybill_number',
        'waybill_image',
        'transport_amount',
        'transport_pay_type',
        'waybill_shipping_charge',
        'doc_courier_name',
        'doc_tracking_number',
        'doc_dispatched_date',
        'doc_status',
        'doc_received_date',
        'delivery_status',
        'delivered_date',
        'status',
        'is_inter_state',
        'subtotal',
        'discount_amount',
        'discount_type',
        'shipping_charge',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tax_amount',
        'roundoff_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'notes',
        'terms',
        'created_by',
        'updated_by',
        'zoho_sync_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'customer_id' => 'required|integer',
        'invoice_date' => 'required|valid_date',
        'due_date' => 'required|valid_date',
    ];

    /**
     * Get invoices with customer details
     */
    public function getInvoicesWithCustomer($filters = [])
    {
        $builder = $this->select('invoices.*, customers.name as customer_name, addresses.city as customer_city')
            ->join('customers', 'customers.id = invoices.customer_id', 'left')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left');

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
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
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
        if (!$invoice)
            return false;

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
            'status' => $status
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

        // Priority 1: Use the flag store on the invoice record if available
        // Priority 2: Use the newly fetched state comparison
        $isInterState = (isset($currentInvoice['is_inter_state']) && $currentInvoice['is_inter_state'] !== null)
            ? (bool) $currentInvoice['is_inter_state']
            : ($customerStateId != $companyStateId);

        foreach ($items as $item) {
            $itemAmount = $item['quantity'] * $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmount / $subtotal * $totalDiscount) : 0;
            $taxableValue = $itemAmount - $itemDiscount;

            $taxAmount = ($taxableValue * $item['tax_percentage']) / 100;

            if ($isInterState) {
                $igst += $taxAmount;
                $itemModel->update($item['id'], [
                    'igst_rate' => $item['tax_percentage'],
                    'cgst_rate' => 0,
                    'sgst_rate' => 0
                ]);
            } else {
                $cgst += $taxAmount / 2;
                $sgst += $taxAmount / 2;
                $itemModel->update($item['id'], [
                    'cgst_rate' => $item['tax_percentage'] / 2,
                    'sgst_rate' => $item['tax_percentage'] / 2,
                    'igst_rate' => 0
                ]);
            }
        }

        $totalTax = $cgst + $sgst + $igst;
        $total = ($subtotal - $totalDiscount) + $totalTax + $shippingCharge + $roundoffAmount;

        // Explicitly set non-applicable taxes to 0 to avoid residual values
        $updateData = [
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'total_amount' => $total,
            'balance' => $total - ($currentInvoice['paid_amount'] ?? 0)
        ];

        if ($isInterState) {
            $updateData['igst_amount'] = $igst;
            $updateData['cgst_amount'] = 0;
            $updateData['sgst_amount'] = 0;
            $updateData['is_inter_state'] = 1;
        } else {
            $updateData['cgst_amount'] = $cgst;
            $updateData['sgst_amount'] = $sgst;
            $updateData['igst_amount'] = 0;
            $updateData['is_inter_state'] = 0;
        }

        $this->update($invoiceId, $updateData);

        // Post-update verification
        $updatedInvoice = $this->find($invoiceId);
        if ($isInterState && (!empty($updatedInvoice['cgst_amount']) && $updatedInvoice['cgst_amount'] > 0 || !empty($updatedInvoice['sgst_amount']) && $updatedInvoice['sgst_amount'] > 0)) {
            log_message('error', "InvoiceModel::calculateGST mismatch: Invoice {$invoiceId} marked inter-state but cgst/sgst non-zero. cgst={$updatedInvoice['cgst_amount']}, sgst={$updatedInvoice['sgst_amount']}, igst={$updatedInvoice['igst_amount']}");
        }

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
