<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table            = 'vehicles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'vehicle_name',
        'vehicle_model',
        'vehicle_number',
        'vehicle_type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'vehicle_number' => 'required|min_length[3]|max_length[50]',
        'vehicle_type'   => 'required|in_list[Regular,Over Dimensional Cargo]',
        'status'         => 'permit_empty|in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'vehicle_number' => [
            'required' => 'Vehicle Number is mandatory.',
        ],
    ];

    /**
     * Get active vehicles for select dropdowns
     */
    public function getActiveVehicles(): array
    {
        return $this->where('status', 'active')
            ->orderBy('vehicle_number', 'ASC')
            ->findAll();
    }
}
