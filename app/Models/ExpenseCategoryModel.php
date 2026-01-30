<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseCategoryModel extends Model
{
    protected $table            = 'expense_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['category_name', 'description', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'category_name' => 'required|min_length[3]|is_unique[expense_categories.category_name,id,{id}]',
        'status'        => 'required|in_list[active,inactive]',
    ];

    /**
     * Get categories with filters
     */
    public function getCategoriesWithFilters($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->like('category_name', $filters['search']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('category_name', 'ASC')->get()->getResultArray();
    }
}
