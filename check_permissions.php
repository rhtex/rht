<?php

$db = \Config\Database::connect();

$modules = $db->table('modules')->get()->getResultArray();
$roles = $db->table('roles')->get()->getResultArray();
$adminRole = null;

foreach ($roles as $role) {
    if (strtolower($role['name']) == 'admin' || $role['id'] == 1) {
        $adminRole = $role;
        break;
    }
}

if (!$adminRole) {
    echo "Admin role not found.\n";
    exit;
}

echo "Admin Role: " . $adminRole['name'] . " (ID: " . $adminRole['id'] . ")\n\n";

foreach ($modules as $module) {
    echo "Module: " . $module['name'] . " (" . $module['module_slug'] . ")\n";
    
    $permissions = $db->table('permissions')
                      ->where('module_id', $module['id'])
                      ->get()->getResultArray();
    
    foreach ($permissions as $permission) {
        $assigned = $db->table('role_permissions')
                       ->where('role_id', $adminRole['id'])
                       ->where('permission_id', $permission['id'])
                       ->countAllResults();
        
        $status = $assigned ? "[OK]" : "[MISSING]";
        echo "  - " . $permission['name'] . " (" . $permission['permission_slug'] . ") " . $status . "\n";
        
        if (!$assigned) {
            $db->table('role_permissions')->insert([
                'role_id' => $adminRole['id'],
                'permission_id' => $permission['id']
            ]);
            echo "    -> Assigned to Admin.\n";
        }
    }
    echo "\n";
}
