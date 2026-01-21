<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixPermissionsUrgent extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Get Admin Role ID
        $adminRole = $db->table('roles')->where('role_name', 'admin')->get()->getRow();
        if (!$adminRole) {
            // Fallback to ID 1 if 'admin' role not found (unlikely)
            $adminRoleId = 1;
        } else {
            $adminRoleId = $adminRole->id;
        }

        // 2. Define Modules and Permissions to Ensure
        $modules = [
            'country'      => ['view', 'create', 'edit', 'delete'],
            'state'        => ['view', 'create', 'edit', 'delete'],
            'agent'        => ['view', 'create', 'edit', 'delete'],
            'transport'    => ['view', 'create', 'edit', 'delete'],
            'expense'      => ['view', 'create', 'edit', 'delete'],
            'bank_account' => ['view', 'create', 'edit', 'delete'],
            'users'        => ['view', 'create', 'edit', 'delete'], // Ensure these keys exist
            'roles'        => ['view', 'create', 'edit', 'delete'],
            'settings'     => ['view', 'edit'],
            'employee'     => ['view', 'create', 'edit', 'delete'],
            'attendance'   => ['view', 'create'],
            'loan'         => ['view', 'create', 'edit', 'delete'],
            'salary'       => ['view', 'create', 'edit', 'delete'],
        ];

        // Also map legacy keys if needed, but let's stick to the standard 'slug.action'
        // For 'users', we used 'user.view' in the past. 
        // Let's explicitly check and fix 'user' vs 'users'
        // Ideally we want 'user.view' (singular) to match the code in Sidebar and Routes.
        
        $keyMapping = [
            'users' => 'user',
            'roles' => 'role',
            'settings' => 'setting',
            // others are already singular in key
        ];

        foreach ($modules as $slug => $actions) {
            
            // 2a. Ensure Module Exists
            $moduleSlug = $slug;
            $moduleName = ucfirst(str_replace('_', ' ', $slug));
            
            $existingMod = $db->table('modules')->where('module_slug', $moduleSlug)->get()->getRow();
            if (!$existingMod) {
                $db->table('modules')->insert([
                    'module_name' => $moduleName,
                    'module_slug' => $moduleSlug,
                    'status'      => 'active'
                ]);
                $moduleId = $db->insertID();
            } else {
                $moduleId = $existingMod->id;
            }

            // 2b. Determine Permission Key Base
            $baseKey = $keyMapping[$slug] ?? $slug;

            foreach ($actions as $action) {
                $permKey = $baseKey . '.' . $action;
                $permName = ucfirst($action) . ' ' . $moduleName; // e.g. View Country

                // 3. Ensure Permission Exists
                $existingPerm = $db->table('permissions')->where('permission_key', $permKey)->get()->getRow();
                $permId = null;
                
                if (!$existingPerm) {
                    $db->table('permissions')->insert([
                        'permission_key'  => $permKey,
                        'permission_name' => $permName,
                        'module_id'       => $moduleId,
                    ]);
                    $permId = $db->insertID();
                } else {
                    $permId = $existingPerm->id;
                }

                // 4. Assign to Admin Role
                $hasRolePerm = $db->table('role_permissions')
                    ->where('role_id', $adminRoleId)
                    ->where('permission_id', $permId)
                    ->countAllResults() > 0;
                
                if (!$hasRolePerm) {
                    $db->table('role_permissions')->insert([
                        'role_id'       => $adminRoleId,
                        'permission_id' => $permId
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // No explicit down
    }
}
