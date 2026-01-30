<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name', 'contact_person', 'email', 'website', 'phone', 'whatsapp_number', 'gst_type', 
        'gstin', 'pan_number', 'opening_balance', 'balance_type', 'credit_limit', 'credit_period_days',
        'notes', 'status', 'agent_id', 'zoho_contact_id', 'zoho_sync_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[150]',
        'phone'    => 'required|min_length[10]|max_length[15]',
        'gst_type' => 'required|in_list[Regular,Composition,Unregistered,Consumer]',
        'gstin'    => 'permit_empty|exact_length[15]|alpha_numeric',
        'pan_number' => 'permit_empty|exact_length[10]|alpha_numeric',
        'opening_balance' => 'permit_empty|decimal',
        'balance_type' => 'required|in_list[Dr,Cr]',
        'credit_limit' => 'permit_empty|decimal',
        'credit_period_days' => 'permit_empty|integer',
    ];

    /**
     * Get customers with pending balance and city
     */
    public function getCustomersWithFilters($filters = [], $limit = 0, $offset = 0)
    {
        $builder = $this->builder();
        
        // Subquery for pending balance: (Opening Balance based on type) + SUM(invoices.balance)
        $pendingBalanceSql = "(
            CASE 
                WHEN customers.balance_type = 'Dr' THEN customers.opening_balance 
                ELSE -customers.opening_balance 
            END + 
            COALESCE((SELECT SUM(balance) FROM invoices WHERE customer_id = customers.id), 0)
        )";

        $builder->select('customers.*, addresses.city, ' . $pendingBalanceSql . ' as pending_balance')
                ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('customers.name', $filters['search'])
                ->orLike('customers.phone', $filters['search'])
                ->orLike('addresses.city', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['agent_id'])) {
            $builder->where('customers.agent_id', $filters['agent_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('customers.status', $filters['status']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'customers.id';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        
        // Map alias to full column if needed
        if ($sortBy == 'pending_balance') {
            $builder->orderBy($pendingBalanceSql, $sortOrder, false);
        } else {
            $builder->orderBy($sortBy, $sortOrder);
        }

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }
}
