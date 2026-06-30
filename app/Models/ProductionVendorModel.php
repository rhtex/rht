<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionVendorModel extends Model
{
    protected $table            = 'production_vendors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'business_type', 'gst_number', 'pan_number', 'phone', 'address', 'location', 'status', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name'          => 'required|min_length[3]|max_length[100]',
        'business_type' => 'permit_empty|max_length[100]',
        'gst_number'    => 'permit_empty|min_length[15]|max_length[15]|is_unique[production_vendors.gst_number,id,{id}]',
        'pan_number'    => 'permit_empty|min_length[10]|max_length[10]',
        'phone'         => 'permit_empty',
        'status'        => 'in_list[active,inactive]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get vendors with filters
     */
    public function getVendorsWithFilters($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('name', $filters['search'])
                    ->orLike('gst_number', $filters['search'])
                    ->orLike('pan_number', $filters['search'])
                    ->orLike('phone', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }
}
