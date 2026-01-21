<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckPermissions extends BaseCommand
{
    protected $group       = 'Debug';
    protected $name        = 'debug:permissions';
    protected $description = 'Displays current permissions for Role ID 1';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        CLI::write("--- Modules ---", "yellow");
        $modules = $db->table('modules')->get()->getResultArray();
        foreach($modules as $m) {
            CLI::write("ID: {$m['id']} | Name: " . ($m['module_name'] ?? 'N/A') . " | Slug: " . ($m['module_slug'] ?? 'N/A'));
        }

        CLI::write("\n--- Permissions for Role 1 ---", "yellow");
        $rolePerms = $db->table('role_permissions')
            ->select('permissions.permission_key')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_id', 1)
            ->get()->getResultArray();
            
        $keys = array_column($rolePerms, 'permission_key');
        CLI::write('Total: ' . count($keys), 'green');
        
        $check = [
            'expense.view', 'expense.create',
            'bank_account.view', 'country.view',
            'user.view', 'role.view'
        ];
        
        foreach($check as $k) {
            if(in_array($k, $keys)) {
                CLI::write(" - $k: FOUND", 'green');
            } else {
                CLI::write(" - $k: MISSING", 'red');
            }
        }
    }
}
