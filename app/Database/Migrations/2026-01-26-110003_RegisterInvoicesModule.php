<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterInvoicesModule extends Migration
{
    public function up()
    {
        // Check if module already exists
        $module = $this->db->table('modules')->where('module_name', 'Invoices')->get()->getRowArray();
        
        if (!$module) {
            $this->db->table('modules')->insert([
                'module_name' => 'Invoices',
                'module_slug' => 'invoices',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $moduleId = $this->db->insertID();
        } else {
            $moduleId = $module['id'];
        }

        // Insert permissions for Invoices module
        $permissions = [
            'invoice.view'   => 'View Invoices',
            'invoice.create' => 'Create Invoices',
            'invoice.edit'   => 'Edit Invoices',
            'invoice.delete' => 'Delete/Void Invoices',
        ];

        foreach ($permissions as $name => $desc) {
            $exists = $this->db->table('permissions')->where('permission_key', $name)->get()->getRowArray();
            if (!$exists) {
                $this->db->table('permissions')->insert([
                    'module_id'       => $moduleId,
                    'permission_key'  => $name,
                    'permission_name' => $desc,
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Auto-assign all Invoices permissions to Admin role (role_id = 1)
        $invoicePermissions = $this->db->table('permissions')
                                      ->where('module_id', $moduleId)
                                      ->get()
                                      ->getResultArray();

        foreach ($invoicePermissions as $permission) {
            // Check if already assigned
            $exists = $this->db->table('role_permissions')
                              ->where('role_id', 1)
                              ->where('permission_id', $permission['id'])
                              ->get()
                              ->getNumRows();

            if ($exists == 0) {
                $this->db->table('role_permissions')->insert([
                    'role_id'       => 1,
                    'permission_id' => $permission['id'],
                ]);
            }
        }
    }

    public function down()
    {
        // Get module ID
        $module = $this->db->table('modules')->where('module_name', 'Invoices')->get()->getRowArray();
        
        if ($module) {
            // Delete permissions
            $this->db->table('permissions')->where('module_id', $module['id'])->delete();
            
            // Delete module
            $this->db->table('modules')->where('id', $module['id'])->delete();
        }
    }
}
