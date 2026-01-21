<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterZohoSettingsModule extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Add Module
        $db->table('modules')->insert([
            'module_name' => 'Zoho Integration',
            'module_slug' => 'zoho_settings',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $moduleId = $db->insertID();

        // 2. Add Permissions
        $permissions = [
            ['module_id' => $moduleId, 'permission_key' => 'zoho.view', 'permission_name' => 'View Zoho Settings'],
            ['module_id' => $moduleId, 'permission_key' => 'zoho.edit', 'permission_name' => 'Edit Zoho Settings'],
            ['module_id' => $moduleId, 'permission_key' => 'zoho.sync', 'permission_name' => 'Sync with Zoho'],
        ];
        $db->table('permissions')->insertBatch($permissions);

        // 3. Assign to Admin (Role 1)
        $perms = $db->table('permissions')->where('module_id', $moduleId)->get()->getResultArray();
        foreach ($perms as $p) {
            $db->table('role_permissions')->insert([
                'role_id'       => 1,
                'permission_id' => $p['id']
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $module = $db->table('modules')->where('module_slug', 'zoho_settings')->get()->getRow();
        if ($module) {
            $db->table('role_permissions')->whereIn('permission_id', function($builder) use ($module) {
                return $builder->select('id')->from('permissions')->where('module_id', $module->id);
            })->delete();
            $db->table('permissions')->where('module_id', $module->id)->delete();
            $db->table('modules')->where('id', $module->id)->delete();
        }
    }
}
