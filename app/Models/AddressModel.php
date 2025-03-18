<?php

namespace App\Models;

use CodeIgniter\Model;

class AddressModel extends Model
{
    protected $table = 'customer_addresses';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id', 'address_type', 'attention', 'country', 
        'street1', 'street2', 'city', 'state', 'pin_code', 
        'phone', 'fax', 'created_by', 'updated_by'
    ];
    protected $useTimestamps = true; // Automatically handle created_at and updated_at
}
