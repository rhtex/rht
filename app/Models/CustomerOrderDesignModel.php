<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerOrderDesignModel extends Model
{
    protected $table            = 'customer_order_designs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'customer_order_id',
        'design_no',
        'product_id',
        'product_name',
        'rate',
        'image',
    ];

    public function getByOrderId(int $orderId): array
    {
        return $this->where('customer_order_id', $orderId)
            ->orderBy('design_no', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
