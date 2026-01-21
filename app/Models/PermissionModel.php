<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['permission_name', 'permission_key', 'module_id'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'permission_name' => 'required|min_length[3]',
        'permission_key'  => 'required|is_unique[permissions.permission_key,id,{id}]',
        'module_id'       => 'required|is_not_unique[modules.id]',
    ];

    public function getPermissionsWithModule()
    {
        return $this->select('permissions.*, modules.module_name')
            ->join('modules', 'modules.id = permissions.module_id')
            ->findAll();
    }
}
