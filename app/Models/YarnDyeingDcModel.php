<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnDyeingDcModel extends Model
{
    protected $table            = 'production_yarn_dyeing_dcs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['dc_number', 'dc_date', 'vendor_name', 'expected_return_date', 'vehicle_details', 'status', 'remarks', 'created_by', 'updated_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'dc_number'     => 'required|is_unique[production_yarn_dyeing_dcs.dc_number,id,{id}]',
        'dc_date'       => 'required|valid_date[Y-m-d]',
        'vendor_name'   => 'required|min_length[3]|max_length[255]',
        'status'        => 'in_list[Open,Partially Received,Completed,Cancelled]',
    ];
}
