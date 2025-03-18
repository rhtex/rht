<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $allowedFields = [
        'zoho_contact_id', 'contact_type', 'company_name', 'contact_name',
        'customer_sub_type', 'credit_limit', 'gst_no', 'preferred_transport','payment_terms',
        'created_time', 'updated_time', 'tax_type', 'contact_no', 'email', 'agent'
    ];

    protected $useTimestamps = false; // Set to true if your table has `created_at` and `updated_at` fields
}
