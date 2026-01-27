<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AssignSuperAdminPermissions extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Get Super Admin Role ID (Assume 1, or fetch by name)
        $roleId = 1; // Default for Super Admin

        // Get all permission IDs
        $query = $db->table('permissions')->select('id')->get();
        $permissions = $query->getResultArray();

        $rolePermsModel = $db->table('role_permissions');
        $added = 0;

        foreach ($permissions as $perm) {
            // Check if exists
            $exists = $rolePermsModel->where('role_id', $roleId)
                                     ->where('permission_id', $perm['id'])
                                     ->countAllResults();

            if ($exists == 0) {
                $rolePermsModel->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $perm['id'],
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
                $added++;
            }
        }

        echo "Assigned $added new permissions to Role ID $roleId.\n";
    }
}
