<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerOrderModel extends Model
{
    protected $table            = 'customer_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'customer_id',
        'product_id',
        'product_name',
        'design_no',
        'rate',
        'order_date',
        'status',
        'image',
        'notes',
        'created_by',
        'updated_by',
    ];

    public const STATUSES = ['Pending', 'In Progress', 'Ready', 'Completed', 'Cancelled'];

    public function getWithCustomer(?string $search = null, ?string $status = null, ?int $customerId = null): array
    {
        $builder = $this->select('customer_orders.*, customers.name as customer_name')
            ->join('customers', 'customers.id = customer_orders.customer_id', 'left');

        if ($search !== null && trim($search) !== '') {
            $s = trim($search);
            $builder->groupStart()
                ->like('customer_orders.design_no', $s)
                ->orLike('customer_orders.product_name', $s)
                ->orLike('customers.name', $s)
                ->groupEnd();
        }

        if ($status !== null && $status !== '') {
            $builder->where('customer_orders.status', $status);
        }

        if ($customerId !== null && $customerId > 0) {
            $builder->where('customer_orders.customer_id', $customerId);
        }

        return $builder->orderBy('customer_orders.order_date', 'DESC')
            ->orderBy('customer_orders.id', 'DESC')
            ->findAll();
    }

    public function getOrdersForCustomer(int $customerId): array
    {
        return $this->where('customer_id', $customerId)
            ->where('status !=', 'Cancelled')
            ->orderBy('order_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}
