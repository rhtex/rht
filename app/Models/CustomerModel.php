<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name', 'contact_person', 'email', 'website', 'phone', 'whatsapp_number', 'gst_type', 
        'gstin', 'pan_number', 'opening_balance', 'balance_type', 'credit_limit', 'credit_period_days',
        'notes', 'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[150]',
        'phone'    => 'required|min_length[10]|max_length[15]',
        'gst_type' => 'required|in_list[Regular,Composition,Unregistered,Consumer]',
        'gstin'    => 'permit_empty|exact_length[15]|alpha_numeric',
        'pan_number' => 'permit_empty|exact_length[10]|alpha_numeric',
        'opening_balance' => 'permit_empty|decimal',
        'balance_type' => 'required|in_list[Dr,Cr]',
        'credit_limit' => 'permit_empty|decimal',
        'credit_period_days' => 'permit_empty|integer',
    ];
}
