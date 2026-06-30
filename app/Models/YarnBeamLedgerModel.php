<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnBeamLedgerModel extends Model
{
    protected $table            = 'production_beam_ledger';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'beam_id', 'transaction_date', 'transaction_type', 'reference_id',
        'from_location', 'to_location', 'status_from', 'status_to',
        'yarn_details', 'remarks', 'created_by'
    ];
    protected $useTimestamps    = false;
}
