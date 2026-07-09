<?php

namespace App\Models;

use CodeIgniter\Model;

class WeaverLedgerModel extends Model
{
    protected $table            = 'weaver_ledgers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'weaver_id', 'title', 'principal_amount', 'interest_rate',
        'total_amount_due', 'balance_amount', 'status', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'weaver_id'        => 'required|integer',
        'title'            => 'required|max_length[255]',
        'principal_amount' => 'required|numeric',
        'interest_rate'    => 'required|numeric',
    ];
}
