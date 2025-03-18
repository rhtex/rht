<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportModel extends Model
{
    protected $table = 'transports';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'gst_number', 
        'transporter_name', 
        'transport_address', 
        'branch_name', 
        'branch_phone', 
        'branch_mobile', 
        'branch_email', 
        'customercare_email', 
        'customer_care_phone'
    ];
}
