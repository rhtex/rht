<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductItemModel extends Model
{
    protected $table            = 'product_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id', 'barcode', 'received_image', 'verified_image', 'purchase_price', 'selling_price',
        'status', 'is_approved', 'approved_by', 'approved_at', 'created_by', 'sold_date', 
        'sold_reference_no', 'sold_type', 'vendor_id', 'vendor_invoice_no', 'remarks', 
        'is_damaged', 'damage_owner', 'damage_price', 'rejection_reason', 'rejection_image', 'return_action'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'product_id' => 'required|integer',
        'barcode'    => 'required|is_unique[product_items.barcode,id,{id}]',
        'status'     => 'required|in_list[received,available,sold,damaged,returned,rejected]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    public function getItemsByProduct($productId, $filters = [])
    {
        $builder = $this->select('product_items.*, vendors.name as vendor_name, creators.name as creator_name, approvers.name as approver_name')
                    ->join('vendors', 'vendors.id = product_items.vendor_id', 'left')
                    ->join('users as creators', 'creators.id = product_items.created_by', 'left')
                    ->join('users as approvers', 'approvers.id = product_items.approved_by', 'left')
                    ->where('product_id', $productId);

        if (!empty($filters['status'])) {
            $builder->where('product_items.status', $filters['status']);
        }

        if (!empty($filters['is_approved'])) {
            $builder->where('product_items.is_approved', $filters['is_approved']);
        }

        return $builder->findAll();
    }

    public function getAllItems($filters = [])
    {
        $builder = $this->select('product_items.*, products.product_name, creators.name as creator_name')
                    ->join('products', 'products.id = product_items.product_id', 'left')
                    ->join('users as creators', 'creators.id = product_items.created_by', 'left');

        if (!empty($filters['status'])) {
            $builder->where('product_items.status', $filters['status']);
        }

        if (!empty($filters['is_approved'])) {
            $builder->where('product_items.is_approved', $filters['is_approved']);
        }

        if (!empty($filters['barcode'])) {
            $builder->like('product_items.barcode', $filters['barcode']);
        }

        return $builder->orderBy('product_items.created_at', 'DESC')->findAll();
    }

    public function getRejectedItems($filters = [])
    {
        $builder = $this->select('product_items.*, products.product_name, vendors.name as vendor_name, users.name as rejected_by_name')
                    ->join('products', 'products.id = product_items.product_id', 'left')
                    ->join('vendors', 'vendors.id = product_items.vendor_id', 'left')
                    ->join('users', 'users.id = product_items.approved_by', 'left') // approved_by tracks rejector
                    ->where('product_items.status', 'rejected')
                    ->where('product_items.is_approved', 'Rejected');

         if (!empty($filters['vendor_id'])) {
            $builder->where('product_items.vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['return_action'])) {
            $builder->where('product_items.return_action', $filters['return_action']);
        } else {
            // Default to showing all if no filter, or maybe just Pending?
            // User asked for "Pending Returns" feature showing "all rejected items". 
            // So default shows all, but UI might default filter to Pending.
        }
        
        return $builder->orderBy('product_items.updated_at', 'DESC')->findAll();
    }
}
