<?php
namespace App\Models;

use CodeIgniter\Model;

class ModulePermissionModel extends Model
{
    protected $table = 'module_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'module_id', 'create_permission', 'read_permission', 'update_permission', 'delete_permission'];
}


?>