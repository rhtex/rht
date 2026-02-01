<?php require_once __DIR__ . '/debug_helpers.php'; ?>

namespace App\Controllers;
use App\Controllers\BaseController;
class DebugController extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        
        echo "<h1>Debug Info</h1>";
        
        // 1. Check Modules
        $modules = $db->table('modules')->get()->getResultArray();
        echo "<h2>Modules (" . count($modules) . ")</h2>";
        echo "<pre>"; safe_print_r($modules); echo "</pre>";

        // 2. Check Permissions Count
        $perms = $db->table('permissions')->get()->getResultArray();
        echo "<h2>Permissions (" . count($perms) . ")</h2>";
        // echo "<pre>"; safe_print_r($perms); echo "</pre>";

        // 3. Check Role 1 Permissions
        $rolePerms = $db->table('role_permissions')
            ->select('permissions.permission_key, permissions.permission_name')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_id', 1)
            ->get()->getResultArray();
            
        echo "<h2>Role 1 (Super Admin) Permissions (" . count($rolePerms) . ")</h2>";
        echo "<pre>"; safe_print_r($rolePerms); echo "</pre>";
        
        // 4. Check specific keys
        $keys = array_column($rolePerms, 'permission_key');
        echo "Has expense.view? " . (in_array('expense.view', $keys) ? 'YES' : 'NO') . "<br>";
        echo "Has bank_account.view? " . (in_array('bank_account.view', $keys) ? 'YES' : 'NO') . "<br>";
    }
}
