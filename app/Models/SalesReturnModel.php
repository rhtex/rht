<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesReturnModel extends Model
{
    protected $table = 'sales_returns';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'invoice_id',
        'return_number',
        'return_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'reason',
        'status',
        'is_inter_state',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'customer_id' => 'required|integer',
        'return_date' => 'required|valid_date',
    ];

    /**
     * Get returns with filters
     */
    public function getReturnsWithFilters($filters = [])
    {
        $builder = $this->select('sales_returns.*, customers.name as customer_name, invoices.invoice_number')
            ->join('customers', 'customers.id = sales_returns.customer_id')
            ->join('invoices', 'invoices.id = sales_returns.invoice_id', 'left');

        if (!empty($filters['customer_id'])) {
            $builder->where('sales_returns.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('sales_returns.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('sales_returns.return_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('sales_returns.return_date <=', $filters['date_to']);
        }

        $builder->orderBy('sales_returns.return_date', 'DESC')->orderBy('sales_returns.id', 'DESC');

        if (isset($filters['limit'])) {
            $limit = (int) $filters['limit'];
            $offset = (int) ($filters['offset'] ?? 0);
            return $builder->findAll($limit, $offset);
        }

        return $builder->findAll();
    }

    /**
     * Get returns by customer ID
     */
    public function getReturnsByCustomer($customerId)
    {
        return $this->select('sales_returns.*, invoices.invoice_number')
            ->join('invoices', 'invoices.id = sales_returns.invoice_id', 'left')
            ->where('sales_returns.customer_id', $customerId)
            ->orderBy('sales_returns.return_date', 'DESC')
            ->findAll();
    }

    /**
     * Get return by ID with items, customer and invoice details
     */
    public function getReturnById($id)
    {
        $return = $this->select('sales_returns.*, customers.name as customer_name, customers.contact_person as customer_contact_person, customers.phone as customer_phone, customers.email as customer_email, customers.gstin as customer_gstin, customers.gst_type as customer_gst_type, addresses.address_line1 as customer_address1, addresses.address_line2 as customer_address2, addresses.city as customer_city, addresses.pincode as customer_pincode, states.id as customer_state_id, states.name as customer_state, invoices.invoice_number, invoices.invoice_date, invoices.total_amount as invoice_total_amount')
            ->join('customers', 'customers.id = sales_returns.customer_id', 'left')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->join('invoices', 'invoices.id = sales_returns.invoice_id', 'left')
            ->find($id);

        if ($return) {
            $itemModel = new \App\Models\SalesReturnItemModel();
            $return['items'] = $itemModel->getItemsByReturn($id);
        }

        return $return;
    }

    /**
     * Generate next return number
     */
    public function generateReturnNumber()
    {
        $prefix = 'SRTN-' . date('Ym') . '-';
        $lastReturn = $this->like('return_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastReturn) {
            $lastNumber = (int) substr($lastReturn['return_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
