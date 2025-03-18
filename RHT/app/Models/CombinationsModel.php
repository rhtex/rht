<?php

namespace App\Models;

use CodeIgniter\Model;

class CombinationsModel extends Model
{
    protected $table = 'combinations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['body_color', 'border_color', 'approval', 'status'];
}
