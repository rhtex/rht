<?php

namespace App\Models;

use CodeIgniter\Model;

class StateModel extends Model
{
    protected $table = 'state';
    protected $primaryKey = 'state_code'; // Assuming state_code is the primary key

    public function getStates()
    {
        return $this->findAll();
    }
}


?>