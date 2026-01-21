<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSettingsPermission extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Add Setting Module
        $existing = $db->table('modules')->where('module_slug', 'settings')->get()->getRow();
        if (!$existing) {
            $db->table('modules')->insert([
                'module_name' => 'Settings',
                'module_slug' => 'settings',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            $moduleId = $db->insertID();
        } else {
            $moduleId = $existing->id;
        }

        // 2. Add Permissions
        $permissions = [
            ['module_id' => $moduleId, 'permission_key' => 'setting.view', 'permission_name' => 'View Settings'],
            ['module_id' => $moduleId, 'permission_key' => 'setting.edit', 'permission_name' => 'Edit Settings'],
        ];

        foreach ($permissions as $p) {
            $p['created_at'] = date('Y-m-d H:i:s');
            $p['updated_at'] = date('Y-m-d H:i:s');
            $db->table('permissions')->insert($p);
            $permissionId = $db->insertID();

            // 3. Assign to Admin Role (Role ID 1)
            $db->table('role_permissions')->insert([
                'role_id'       => 1,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('permissions')->whereIn('permission_key', ['setting.view', 'setting.edit'])->delete();
        $db->table('modules')->where('module_name', 'Settings')->delete();
    }
}
