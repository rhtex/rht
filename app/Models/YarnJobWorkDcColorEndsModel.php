<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnJobWorkDcColorEndsModel extends Model
{
    protected $table            = 'production_yarn_job_work_dc_color_ends';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['dc_id', 'color', 'ends_count'];
    protected $useTimestamps    = false;
}
