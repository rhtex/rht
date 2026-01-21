<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddModuleAndPermissionManagement extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ensure 'users' module exists (for grouping)
        $userMod = $db->table('modules')->where('module_slug', 'users')->get()->getRow();
        $moduleId = $userMod ? $userMod->id : 1;

        // 2. Define new permissions
        $perms = [
            'module.view'      => 'View Modules',
            'module.create'    => 'Create Module',
            'module.edit'      => 'Edit Module',
            'module.delete'    => 'Delete Module',
            'permission.view'  => 'View Permission Keys',
            'permission.create'=> 'Create Permission Key',
            'permission.edit'  => 'Edit Permission Key',
            'permission.delete'=> 'Delete Permission Key',
        ];

        foreach ($perms as $key => $name) {
            $existing = $db->table('permissions')->where('permission_key', $key)->get()->getRow();
            if (!$existing) {
                $db->table('permissions')->insert([
                    'permission_key'  => $key,
                    'permission_name' => $name,
                    'module_id'       => $moduleId,
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s')
                ]);
                $permId = $db->insertID();
            } else {
                $permId = $existing->id;
            }

            // 3. Assign to Admin (Role 1)
            $hasPerm = $db->table('role_permissions')
                ->where('role_id', 1)
                ->where('permission_id', $permId)
                ->countAllResults() > 0;
            
            if (!$hasPerm) {
                $db->table('role_permissions')->insert([
                    'role_id'       => 1,
                    'permission_id' => $permId
                ]);
            }
        }
    }

    public function down()
    {
    }
}
