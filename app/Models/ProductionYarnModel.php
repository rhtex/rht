<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionYarnModel extends Model
{
    protected $table            = 'production_yarns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'yarn_count', 'yarn_type', 'color', 'brand', 'stock_kg', 'status', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name'       => 'required|min_length[3]|max_length[255]',
        'yarn_count' => 'required|max_length[50]',
        'yarn_type'  => 'required|in_list[Dyed,Raw]',
        'color'      => 'permit_empty|max_length[100]',
        'brand'      => 'permit_empty|max_length[100]',
        'stock_kg'   => 'required|numeric|greater_than_equal_to[0]',
        'status'     => 'in_list[active,inactive]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get yarns with filters
     */
    public function getYarnsWithFilters($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('name', $filters['search'])
                    ->orLike('yarn_count', $filters['search'])
                    ->orLike('color', $filters['search'])
                    ->orLike('brand', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['yarn_type'])) {
            $builder->where('yarn_type', $filters['yarn_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }
}
