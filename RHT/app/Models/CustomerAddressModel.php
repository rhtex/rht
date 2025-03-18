<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerAddressModel extends Model
{
    protected $table = 'customer_address';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id', 'address_type', 'address1', 'address2', 'city', 'state', 'country','zip','state_name'
    ];
}


?>