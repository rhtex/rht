<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnJobWorkDcBeamModel extends Model
{
    protected $table            = 'production_yarn_job_work_dc_beams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['dc_id', 'receipt_id', 'returned_status', 'meters', 'sizing_no', 'color', 'return_date', 'beam_number', 'remarks'];
    protected $useTimestamps    = false;
}
