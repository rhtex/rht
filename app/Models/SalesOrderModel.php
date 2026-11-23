<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderModel extends Model
{
    protected $table = 'sales_orders';
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
        'quotation_id',
        'sales_order_number',
        'order_date',
        'shipment_date',
        'reference_number',
        'selection_type',
        'transport_name',
        'waybill_number',
        'status',
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
        'order_date' => 'required|valid_date',
    ];

    public function getOrdersWithCustomer($filters = [])
    {
        $builder = $this->select('sales_orders.*, customers.name as customer_name, addresses.city as customer_city, invoices.invoice_number as converted_invoice_number')
            ->join('customers', 'customers.id = sales_orders.customer_id', 'left')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('invoices', 'invoices.id = sales_orders.converted_to_invoice_id', 'left');

        if (!empty($filters['sales_order_number'])) {
            $builder->like('sales_orders.sales_order_number', trim($filters['sales_order_number']));
        }

        if (!empty($filters['customer_id'])) {
            $builder->where('sales_orders.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('sales_orders.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('sales_orders.order_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('sales_orders.order_date <=', $filters['date_to']);
        }

        $builder->orderBy('sales_orders.order_date', 'DESC')->orderBy('sales_orders.id', 'DESC');

        if (isset($filters['limit'])) {
            $limit = (int) $filters['limit'];
            $offset = (int) ($filters['offset'] ?? 0);
            return $builder->findAll($limit, $offset);
        }

        return $builder->findAll();
    }

    public function getOrderById($id)
    {
        $order = $this->select('sales_orders.*, 
            customers.name as customer_name, 
            customers.gstin as customer_gstin,
            customers.phone as customer_phone,
            customers.whatsapp_number as customer_whatsapp,
            states.id as customer_state_id, 
            states.name as customer_state,
            states.gst_state_code as customer_state_gst_code,
            addresses.address_line1 as customer_address_1,
            addresses.address_line2 as customer_address_2,
            addresses.city as customer_city,
            addresses.pincode as customer_pincode,
            ship_addr.address_line1 as shipping_address_1,
            ship_addr.address_line2 as shipping_address_2,
            ship_addr.city as shipping_city,
            ship_addr.pincode as shipping_pincode,
            ship_states.name as shipping_state,
            agents.agent_name, 
            invoices.invoice_number as converted_invoice_number, 
            quotations.quotation_number as source_quotation_number')
            ->join('customers', 'customers.id = sales_orders.customer_id', 'left')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->join('addresses as ship_addr', 'ship_addr.owner_id = customers.id AND ship_addr.owner_type = "customer" AND ship_addr.address_type = "shipping" AND ship_addr.is_active = 1', 'left')
            ->join('states as ship_states', 'ship_states.id = ship_addr.state_id', 'left')
            ->join('agents', 'agents.id = sales_orders.agent_id', 'left')
            ->join('invoices', 'invoices.id = sales_orders.converted_to_invoice_id', 'left')
            ->join('quotations', 'quotations.id = sales_orders.quotation_id', 'left')
            ->find($id);

        if ($order) {
            $itemModel = new \App\Models\SalesOrderItemModel();
            $order['items'] = $itemModel->getItemsByOrder($id);
        }

        return $order;
    }

    /**
     * Mark a sales order as converted to invoice
     */
    public function markConverted($orderId, $invoiceId)
    {
        return $this->update($orderId, [
            'status'                   => 'Closed',
            'converted_to_invoice_id'  => $invoiceId,
        ]);
    }

    public function generateOrderNumber()
    {
        $prefix = 'SO-' . date('Ym') . '-';
        $last = $this->like('sales_order_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last['sales_order_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
