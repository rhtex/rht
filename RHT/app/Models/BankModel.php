<?php

namespace App\Models;

use CodeIgniter\Model;

class BankModel extends Model
{
    protected $table = 'banks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['branch', 'ifsc_code', 'branch_address', 'account_no', 'account_name', 'customer_id', 'status', 'bank_name', 'balance'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Relationships
    public function statements()
    {
        return $this->hasMany(StatementModel::class, 'bank_id');
    }
}
