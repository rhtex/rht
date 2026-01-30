<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table            = 'vendors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name', 'contact_person', 'email', 'website', 'phone', 'whatsapp_number', 'gst_type', 
        'gstin', 'pan_number', 'opening_balance', 'balance_type', 'notes', 'status',
        'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch', 'zoho_contact_id', 'zoho_sync_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[150]',
        'phone'    => 'required|min_length[10]|max_length[15]',
        'gst_type' => 'required|in_list[Regular,Composition,Unregistered]',
        'gstin'    => 'permit_empty|exact_length[15]|alpha_numeric',
        'pan_number' => 'permit_empty|exact_length[10]|alpha_numeric',
        'opening_balance' => 'permit_empty|decimal',
        'balance_type' => 'required|in_list[Dr,Cr]',
    ];

    /**
     * Get vendors with pending balance and city
     */
    public function getVendorsWithFilters($filters = [], $limit = 0, $offset = 0)
    {
        $builder = $this->builder();
        
        // Subquery for pending balance: (Opening Balance based on type) + SUM(bills.balance)
        $pendingBalanceSql = "(
            CASE 
                WHEN vendors.balance_type = 'Dr' THEN -vendors.opening_balance 
                ELSE vendors.opening_balance 
            END + 
            COALESCE((SELECT SUM(balance) FROM bills WHERE vendor_id = vendors.id), 0)
        )";
        // Note: For vendors (payables), Cr is usually positive (we owe them), Dr is negative.
        // Wait, normally for a vendor:
        // Opening balance Cr = We owe them (Payable)
        // Opening balance Dr = They owe us (Advance)
        // Bills increase the amount we owe them.
        // So Pending Balance (Payable) = (Cr - Dr) + Sum(Bill Balances)
        
        $builder->select('vendors.*, addresses.city, ' . $pendingBalanceSql . ' as pending_balance')
                ->join('addresses', 'addresses.owner_id = vendors.id AND addresses.owner_type = "vendor" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('vendors.name', $filters['search'])
                ->orLike('vendors.phone', $filters['search'])
                ->orLike('addresses.city', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('vendors.status', $filters['status']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'vendors.id';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        
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
