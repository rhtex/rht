<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationModel extends Model
{
    protected $table = 'quotations';
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
        'quotation_number',
        'quotation_date',
        'expiry_date',
        'reference_number',
        'transport_name',
        'waybill_number',
        'status',
        'converted_to_so_id',
        'converted_to_invoice_id',
        'is_inter_state',
        'subtotal',
        'discount_amount',
        'discount_type',
        'shipping_charge',
        'roundoff_amount',
        'tax_amount',
        'total_amount',
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
        'customer_id' => 'required|integer',
        'quotation_date' => 'required|valid_date',
    ];

    public function getQuotationsWithCustomer($filters = [])
    {
        $builder = $this->select('quotations.*, customers.name as customer_name, addresses.city, sales_orders.id as linked_so_id, sales_orders.sales_order_number as converted_so_number, invoices.id as linked_invoice_id, invoices.invoice_number as converted_invoice_number')
            ->join('customers', 'customers.id = quotations.customer_id', 'left')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('sales_orders', 'sales_orders.id = quotations.converted_to_so_id OR (quotations.converted_to_so_id IS NULL AND sales_orders.quotation_id = quotations.id)', 'left')
            ->join('invoices', 'invoices.id = quotations.converted_to_invoice_id', 'left');

        if (!empty($filters['quotation_number'])) {
            $builder->like('quotations.quotation_number', $filters['quotation_number']);
        }

        if (!empty($filters['customer_id'])) {
            $builder->where('quotations.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('quotations.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('quotations.quotation_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('quotations.quotation_date <=', $filters['date_to']);
        }

        return $builder->orderBy('quotations.created_at', 'DESC')->findAll();
    }

    public function getQuotationById($id)
    {
        $quotation = $this->select('quotations.*, customers.name as customer_name, customers.gstin as customer_gstin, customers.phone as customer_phone, customers.whatsapp_number as customer_whatsapp, agents.agent_name, states.name as customer_state, states.id as customer_state_id, addresses.address_line1 as customer_address1, addresses.address_line2 as customer_address2, addresses.city as customer_city, addresses.pincode as customer_pincode, sales_orders.id as linked_so_id, sales_orders.sales_order_number as converted_so_number, invoices.id as linked_invoice_id, invoices.invoice_number as converted_invoice_number')
            ->join('customers', 'customers.id = quotations.customer_id', 'left')
            ->join('agents', 'agents.id = quotations.agent_id', 'left')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->join('sales_orders', 'sales_orders.id = quotations.converted_to_so_id OR (quotations.converted_to_so_id IS NULL AND sales_orders.quotation_id = quotations.id)', 'left')
            ->join('invoices', 'invoices.id = quotations.converted_to_invoice_id', 'left')
            ->find($id);

        if ($quotation) {
            $itemModel = new \App\Models\QuotationItemModel();
            $quotation['items'] = $itemModel->where('quotation_id', $id)->findAll();
        }

        return $quotation;
    }

    public function markConverted($id, $status = 'Accepted')
    {
        return $this->update($id, ['status' => $status]);
    }

    public function markConvertedToSo($id, $soId)
    {
        return $this->update($id, [
            'status'             => 'Accepted',
            'converted_to_so_id' => $soId,
        ]);
    }

    public function markConvertedToInvoice($id, $invoiceId)
    {
        return $this->update($id, [
            'status'                  => 'Invoiced',
            'converted_to_invoice_id' => $invoiceId,
        ]);
    }

    public function generateQuotationNumber()
    {
        $prefix = 'QTN-' . date('Ym') . '-';
        $last = $this->like('quotation_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last['quotation_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
