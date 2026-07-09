<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionWeaverSettlementModel extends Model
{
    protected $table            = 'production_weaver_settlements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'settlement_number', 'settlement_date', 'weaver_id', 
        'total_wages', 'deductions', 'net_amount', 'status', 
        'remarks', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'settlement_number' => 'required|is_unique[production_weaver_settlements.settlement_number,id,{id}]',
        'settlement_date'   => 'required|valid_date',
        'weaver_id'         => 'required|integer',
        'status'            => 'in_list[Pending,Paid]',
    ];
}
