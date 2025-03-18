<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierAddressModel extends Model
{
    protected $table = 'supplier_address';
    protected $primaryKey = 'address_id';
    protected $allowedFields = [
        'supplier_id', 'address1', 'address2',
        'city', 'state', 'country', 'created_time', 'updated_time','zip','state_name'
    ];
}


?>