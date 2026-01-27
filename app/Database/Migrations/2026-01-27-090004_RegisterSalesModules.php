<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterSalesModules extends Migration
{
    public function up()
    {
        $modules = [
            'Quotations' => 'quotations',
            'Sales Orders' => 'sales_orders'
        ];

        foreach ($modules as $name => $slug) {
            $module = $this->db->table('modules')->where('module_name', $name)->get()->getRowArray();
            
            if (!$module) {
                $this->db->table('modules')->insert([
                    'module_name' => $name,
                    'module_slug' => $slug,
                    'status'      => 'active',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $moduleId = $this->db->insertID();
            } else {
                $moduleId = $module['id'];
            }

            // Permissions for each module
            $prefix = rtrim($slug, 's'); // quotation, sales_order
            $permissions = [
                "$prefix.view"   => "View $name",
                "$prefix.create" => "Create $name",
                "$prefix.edit"   => "Edit $name",
                "$prefix.delete" => "Delete $name",
            ];

            foreach ($permissions as $key => $desc) {
                $exists = $this->db->table('permissions')->where('permission_key', $key)->get()->getRowArray();
                if (!$exists) {
                    $this->db->table('permissions')->insert([
                        'module_id'       => $moduleId,
                        'permission_key'  => $key,
                        'permission_name' => $desc,
                        'created_at'      => date('Y-m-d H:i:s'),
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
                    $permissionId = $this->db->insertID();

                    // Auto-assign to Admin
                    $this->db->table('role_permissions')->insert([
                        'role_id'       => 1,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Not implemented to avoid accidental data loss
    }
}
