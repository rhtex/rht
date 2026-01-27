<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturnShipmentModel extends Model
{
    protected $table            = 'return_shipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'vendor_id', 'reference_no', 'return_date', 'transport_name', 
        'waybill_number', 'waybill_date', 'packages_count', 'ewaybill_number',
        'status', 'item_count', 'notes', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getShipmentsWithVendor()
    {
        return $this->select('return_shipments.*, vendors.name as vendor_name')
                    ->join('vendors', 'vendors.id = return_shipments.vendor_id')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
