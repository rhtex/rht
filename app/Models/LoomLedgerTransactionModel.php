<?php

namespace App\Models;

use CodeIgniter\Model;

class LoomLedgerTransactionModel extends Model
{
    protected $table            = 'loom_ledger_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ledger_id', 'transaction_type', 'amount', 'payment_method', 'reference_number',
        'transaction_date', 'remarks', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'ledger_id'        => 'required|integer',
        'transaction_type' => 'required|in_list[Principal,Interest Addition,Payment,Reversal,Waiveoff]',
        'amount'           => 'required|numeric',
        'transaction_date' => 'required|valid_date',
    ];
}
