<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterAgentPaymentsModule extends Migration
{
    public function up()
    {
        // Check if module already exists
        $existing = $this->db->table('modules')->where('module_slug', 'agent_payments')->get()->getRow();
        if ($existing) {
            $moduleId = $existing->id;
        } else {
            // Insert module
            $this->db->table('modules')->insert([
                'module_name' => 'Agent Payments',
                'module_slug'  => 'agent_payments',
                'description' => 'Manage agent commission payments',
                'icon'        => 'fas fa-hand-holding-usd',
                'parent_id'   => null,
                'sort_order'  => 40,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $moduleId = $this->db->insertID();
        }

        // Insert permissions
        $permissions = [
            [
                'module_id'       => $moduleId,
                'permission_name' => 'View Agent Payments',
                'permission_key'  => 'agent_payments.view',
                'description'     => 'View agent payment records',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'module_id'       => $moduleId,
                'permission_name' => 'Create Agent Payment',
                'permission_key'  => 'agent_payments.create',
                'description'     => 'Record new agent commission payments',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'module_id'       => $moduleId,
                'permission_name' => 'Delete Agent Payment',
                'permission_key'  => 'agent_payments.delete',
                'description'     => 'Delete agent payment records',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'module_id'       => $moduleId,
                'permission_name' => 'View Commission Reports',
                'permission_key'  => 'agent_payments.reports',
                'description'     => 'View agent commission reports',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        // Check if permissions already exist
        $existingPermissions = $this->db->table('permissions')->where('module_id', $moduleId)->countAllResults();
        
        if ($existingPermissions == 0) {
            $this->db->table('permissions')->insertBatch($permissions);
        }

        // Assign all permissions to Admin role (role_id = 1)
        $permissionIds = $this->db->table('permissions')
            ->where('module_id', $moduleId)
            ->get()
            ->getResultArray();

        $rolePermissions = [];
        foreach ($permissionIds as $permission) {
            $rolePermissions[] = [
                'role_id'       => 1, // Admin
                'permission_id' => $permission['id'],
                'created_at'    => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($rolePermissions)) {
            foreach ($rolePermissions as $rp) {
                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $rp['role_id'])
                    ->where('permission_id', $rp['permission_id'])
                    ->countAllResults();
                
                if ($exists == 0) {
                    $this->db->table('role_permissions')->insert($rp);
                }
            }
        }
    }

    public function down()
    {
        // Get module ID
        $module = $this->db->table('modules')
            ->where('module_slug', 'agent_payments')
            ->get()
            ->getRowArray();

        if ($module) {
            // Delete role permissions
            $permissions = $this->db->table('permissions')
                ->where('module_id', $module['id'])
                ->get()
                ->getResultArray();

            $permissionIds = array_column($permissions, 'id');
            if (!empty($permissionIds)) {
                $this->db->table('role_permissions')
                    ->whereIn('permission_id', $permissionIds)
                    ->delete();
            }

            // Delete permissions
            $this->db->table('permissions')
                ->where('module_id', $module['id'])
                ->delete();

            // Delete module
            $this->db->table('modules')
                ->where('id', $module['id'])
                ->delete();
        }
    }
}
