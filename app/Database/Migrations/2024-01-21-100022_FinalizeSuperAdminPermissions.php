<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalizeSuperAdminPermissions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ensure all Modules exist
        $modules = [
            'users'      => 'User Management',
            'employees'  => 'Employee Management',
            'attendance' => 'Attendance Management',
            'payroll'    => 'Payroll Management',
            'settings'   => 'Settings'
        ];

        $moduleIds = [];
        foreach ($modules as $slug => $name) {
            $existing = $db->table('modules')->where('module_name', $name)->get()->getRow();
            if (!$existing) {
                $db->table('modules')->insert([
                    'module_name' => $name,
                    'module_slug' => $slug,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s')
                ]);
                $moduleIds[$name] = $db->insertID();
            } else {
                $moduleIds[$name] = $existing->id;
            }
        }

        // 2. Define all required permissions
        $permissions = [
            ['key' => 'user.view', 'name' => 'View Users', 'module' => 'User Management'],
            ['key' => 'user.create', 'name' => 'Create User', 'module' => 'User Management'],
            ['key' => 'user.edit', 'name' => 'Edit User', 'module' => 'User Management'],
            ['key' => 'user.delete', 'name' => 'Delete User', 'module' => 'User Management'],
            
            ['key' => 'role.view', 'name' => 'View Roles', 'module' => 'User Management'],
            ['key' => 'role.create', 'name' => 'Create Role', 'module' => 'User Management'],
            ['key' => 'role.edit', 'name' => 'Edit Role', 'module' => 'User Management'],
            ['key' => 'role.delete', 'name' => 'Delete Role', 'module' => 'User Management'],
            
            ['key' => 'employee.view', 'name' => 'View Employees', 'module' => 'Employee Management'],
            ['key' => 'employee.create', 'name' => 'Create Employee', 'module' => 'Employee Management'],
            ['key' => 'employee.edit', 'name' => 'Edit Employee', 'module' => 'Employee Management'],
            ['key' => 'employee.delete', 'name' => 'Delete Employee', 'module' => 'Employee Management'],
            
            ['key' => 'attendance.view', 'name' => 'View Attendance', 'module' => 'Attendance Management'],
            ['key' => 'attendance.create', 'name' => 'Create Attendance', 'module' => 'Attendance Management'],
            
            ['key' => 'loan.view', 'name' => 'View Loans', 'module' => 'Payroll Management'],
            ['key' => 'loan.create', 'name' => 'Create Loan', 'module' => 'Payroll Management'],
            ['key' => 'loan.edit', 'name' => 'Edit Loan', 'module' => 'Payroll Management'],
            ['key' => 'loan.delete', 'name' => 'Delete Loan', 'module' => 'Payroll Management'],
            
            ['key' => 'salary.view', 'name' => 'View Salary', 'module' => 'Payroll Management'],
            ['key' => 'salary.create', 'name' => 'Calculate Salary', 'module' => 'Payroll Management'],
            ['key' => 'salary.edit', 'name' => 'Edit Salary Status', 'module' => 'Payroll Management'],
            ['key' => 'salary.delete', 'name' => 'Delete Salary', 'module' => 'Payroll Management'],

            ['key' => 'setting.view', 'name' => 'View Settings', 'module' => 'Settings'],
            ['key' => 'setting.edit', 'name' => 'Edit Settings', 'module' => 'Settings'],
        ];

        foreach ($permissions as $p) {
            $existing = $db->table('permissions')->where('permission_key', $p['key'])->get()->getRow();
            if (!$existing) {
                $db->table('permissions')->insert([
                    'permission_key'  => $p['key'],
                    'permission_name' => $p['name'],
                    'module_id'       => $moduleIds[$p['module']],
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 3. Assign ALL permissions to Admin role (ID 1)
        $allPermissions = $db->table('permissions')->get()->getResult();
        foreach ($allPermissions as $perm) {
            $exists = $db->table('role_permissions')
                ->where('role_id', 1)
                ->where('permission_id', $perm->id)
                ->countAllResults() > 0;
            
            if (!$exists) {
                $db->table('role_permissions')->insert([
                    'role_id'       => 1,
                    'permission_id' => $perm->id
                ]);
            }
        }
    }

    public function down()
    {
        // No down needed for syncing permissions
    }
}
