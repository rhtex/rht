<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table            = 'role_permissions';
    protected $allowedFields    = ['role_id', 'permission_id'];

    public function getPermissionsByRole(int $roleId)
    {
        return $this->select('permissions.permission_key')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_id', $roleId)
            ->findAll();
    }
}
