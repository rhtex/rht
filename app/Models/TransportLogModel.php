<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportLogModel extends Model
{
    protected $table = 'transport_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'price', 'booked_by', 'received_by', 'paid_by', 'transport_no', 'contains', 'others_reason',
        'transport_id', 'sent_date', 'delivered_date', 'delivery_status', 'parcel_type', 'transport_type','sender','receiver','transport_type'
    ];
}
