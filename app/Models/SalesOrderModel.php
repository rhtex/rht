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
        'transport_name',
        'waybill_number',
        'status',
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
        $builder = $this->select('sales_orders.*, customers.name as customer_name')
            ->join('customers', 'customers.id = sales_orders.customer_id', 'left');

        if (!empty($filters['customer_id'])) {
            $builder->where('sales_orders.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('sales_orders.status', $filters['status']);
        }

        return $builder->orderBy('sales_orders.created_at', 'DESC')->findAll();
    }

    public function getOrderById($id)
    {
        $order = $this->select('sales_orders.*, customers.name as customer_name, agents.agent_name')
            ->join('customers', 'customers.id = sales_orders.customer_id', 'left')
            ->join('agents', 'agents.id = sales_orders.agent_id', 'left')
            ->find($id);

        if ($order) {
            $itemModel = new \App\Models\SalesOrderItemModel();
            $order['items'] = $itemModel->where('sales_order_id', $id)->findAll();
        }

        return $order;
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
