<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionAgreementModel extends Model
{
    protected $table            = 'production_agreements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title', 'party_name', 'description', 'start_date', 'end_date', 'agreement_file', 'status', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'title'      => 'required|min_length[3]|max_length[255]',
        'party_name' => 'required|min_length[3]|max_length[255]',
        'status'     => 'in_list[active,expired,terminated]',
        'start_date' => 'permit_empty|valid_date',
        'end_date'   => 'permit_empty|valid_date',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get agreements with filters
     */
    public function getAgreementsWithFilters($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('title', $filters['search'])
                    ->orLike('party_name', $filters['search'])
                    ->orLike('description', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('title', 'ASC')->get()->getResultArray();
    }
}
