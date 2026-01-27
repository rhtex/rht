<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterInventoryModules extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $modules = [
            [
                'module_name' => 'Product Categories',
                'module_slug' => 'product_category',
                'status'      => 'active'
            ],
            [
                'module_name' => 'Products',
                'module_slug' => 'product',
                'status'      => 'active'
            ]
        ];

        foreach ($modules as $mod) {
            $exists = $db->table('modules')->where('module_slug', $mod['module_slug'])->get()->getRow();
            if (!$exists) {
                $db->table('modules')->insert(array_merge($mod, [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]));
                $moduleId = $db->insertID();
            } else {
                $moduleId = $exists->id;
            }

            // Standard Permissions
            $actions = ['view', 'create', 'edit', 'delete'];
            foreach ($actions as $action) {
                $key = $mod['module_slug'] . '.' . $action;
                $name = ucfirst($action) . ' ' . $mod['module_name'];
                
                $pExists = $db->table('permissions')->where('permission_key', $key)->get()->getRow();
                if (!$pExists) {
                    $db->table('permissions')->insert([
                        'module_id'       => $moduleId,
                        'permission_key'  => $key,
                        'permission_name' => $name,
                        'created_at'      => date('Y-m-d H:i:s'),
                        'updated_at'      => date('Y-m-d H:i:s')
                    ]);
                    $permId = $db->insertID();
                } else {
                    $permId = $pExists->id;
                }

                // Assign to Admin (Role 1)
                $rpExists = $db->table('role_permissions')
                    ->where('role_id', 1)
                    ->where('permission_id', $permId)
                    ->get()->getRow();
                
                if (!$rpExists) {
                    $db->table('role_permissions')->insert([
                        'role_id'       => 1,
                        'permission_id' => $permId
                    ]);
                }
            }
        }
    }

    public function down()
    {
    }
}
