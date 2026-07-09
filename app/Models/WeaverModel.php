<?php

namespace App\Models;

use CodeIgniter\Model;

class WeaverModel extends Model
{
    protected $table            = 'weavers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'code', 'phone', 'address', 'address_proof', 'location', 'status', 'created_by', 'updated_by', 'customer_id', 'vendor_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name'   => 'required|min_length[3]|max_length[100]',
        'code'   => 'permit_empty|is_unique[weavers.code,id,{id}]',
        'phone'  => 'permit_empty|min_length[10]',
        'status' => 'in_list[active,inactive]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get weavers with filters
     */
    public function getWeaversWithFilters($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('name', $filters['search'])
                    ->orLike('code', $filters['search'])
                    ->orLike('phone', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }
}
