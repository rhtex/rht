<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentPaymentItemModel extends Model
{
    protected $table            = 'agent_payment_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'agent_payment_id', 'invoice_id', 'commission_amount'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'agent_payment_id'   => 'required|integer',
        'invoice_id'         => 'required|integer',
        'commission_amount'  => 'required|decimal|greater_than[0]',
    ];
}
