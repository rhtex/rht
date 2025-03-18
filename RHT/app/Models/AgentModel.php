<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentModel extends Model
{
    protected $table = 'agents';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'mobile', 'phone', 'address', 'remarks', 'created_by', 'updated_by', 'created_at', 'updated_at'];

    // Optionally, you can define timestamps if needed
    protected $useTimestamps = true;
}
