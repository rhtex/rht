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
        'order_number',
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

    public const STATUSES = ['Active', 'Closed'];

    public function generateOrderNumber(): string
    {
        $last = $this->selectMax('id')->first();
        $next = ((int) ($last['id'] ?? 0)) + 1;
        return 'CO-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getWithCustomer(?string $search = null, ?string $status = null, ?int $customerId = null): array
    {
        $builder = $this->select('customer_orders.*, customers.name as customer_name, COUNT(customer_order_designs.id) as design_count')
            ->join('customers', 'customers.id = customer_orders.customer_id', 'left')
            ->join('customer_order_designs', 'customer_order_designs.customer_order_id = customer_orders.id', 'left')
            ->groupBy('customer_orders.id');

        if ($search !== null && trim($search) !== '') {
            $s = trim($search);
            $builder->groupStart()
                ->like('customer_orders.order_number', $s)
                ->orLike('customers.name', $s)
                ->orLike('customer_order_designs.product_name', $s)
                ->orLike('customer_order_designs.design_no', $s)
                ->groupEnd();
        }

        if ($status !== null && in_array($status, self::STATUSES, true)) {
            $builder->where('customer_orders.status', $status);
        }

        if ($customerId !== null && $customerId > 0) {
            $builder->where('customer_orders.customer_id', $customerId);
        }

        return $builder->orderBy('customer_orders.order_date', 'DESC')
            ->orderBy('customer_orders.id', 'DESC')
            ->findAll();
    }

    public function getOrderWithDesigns(int $id): ?array
    {
        $order = $this->select('customer_orders.*, customers.name as customer_name')
            ->join('customers', 'customers.id = customer_orders.customer_id', 'left')
            ->find($id);
        if (!$order) {
            return null;
        }

        $order['designs'] = (new CustomerOrderDesignModel())->getByOrderId($id);
        return $order;
    }

    public function getActiveDesignsForCustomer(int $customerId): array
    {
        return $this->db->table('customer_order_designs d')
            ->select('d.id, d.customer_order_id, d.design_no, d.product_id, d.product_name, d.rate, d.image, o.order_number, o.order_date')
            ->join('customer_orders o', 'o.id = d.customer_order_id', 'inner')
            ->where('o.customer_id', $customerId)
            ->where('o.status', 'Active')
            ->orderBy('o.order_date', 'DESC')
            ->orderBy('o.id', 'DESC')
            ->orderBy('d.design_no', 'ASC')
            ->get()
            ->getResultArray();
    }
}
