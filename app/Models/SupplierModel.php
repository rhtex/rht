<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    protected $allowedFields = [
        'company_name', 'contact_name', 'msme_registered ', 'tax_type',
        'gst_no', 'payment_terms ', 'contact_no', 'email',
        'created_time', 'updated_time','balance','zoho_contact_id','customer_sub_type'
    ];
}


?>