<?php

namespace App\Models;

use CodeIgniter\Model;

class BankAccountModel extends Model
{
    protected $table            = 'bank_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['bank_name', 'account_number', 'ifsc_code', 'branch_name', 'current_balance', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'bank_name'      => 'required|min_length[2]|max_length[100]',
        'account_number' => 'required|min_length[5]|max_length[50]|is_unique[bank_accounts.account_number,id,{id}]',
        'ifsc_code'      => 'required|min_length[4]|max_length[20]',
        'branch_name'    => 'required|min_length[2]|max_length[100]',
        'status'         => 'required|in_list[active,inactive]',
    ];
}
