<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Modules
        $modules = [
            ['module_name' => 'User Management', 'module_slug' => 'users'],
            ['module_name' => 'Employee Management', 'module_slug' => 'employees'],
            ['module_name' => 'Attendance Management', 'module_slug' => 'attendance'],
            ['module_name' => 'Payroll Management', 'module_slug' => 'payroll'],
            ['module_name' => 'Finance Management', 'module_slug' => 'finance'],
        ];

        $moduleModel = $this->db->table('modules');
        
        foreach ($modules as $mod) {
            // Upsert
            if ($moduleModel->where('module_slug', $mod['module_slug'])->countAllResults() === 0) {
                $moduleModel->insert($mod);
            }
        }

        // 2. Fetch Module IDs
        $mods = [];
        $query = $moduleModel->get();
        foreach ($query->getResult() as $row) {
            $mods[$row->module_slug] = $row->id;
        }

        // 3. Seed Permissions
        $permissions = [
            // User Module
            ['key' => 'user.view', 'name' => 'View Users', 'module' => 'users'],
            ['key' => 'user.create', 'name' => 'Create User', 'module' => 'users'],
            ['key' => 'user.edit', 'name' => 'Edit User', 'module' => 'users'],
            ['key' => 'user.delete', 'name' => 'Delete User', 'module' => 'users'],
            
            // Employee Module
            ['key' => 'employee.view', 'name' => 'View Employees', 'module' => 'employees'],
            ['key' => 'employee.create', 'name' => 'Create Employee', 'module' => 'employees'],
            ['key' => 'employee.edit', 'name' => 'Edit Employee', 'module' => 'employees'],
            ['key' => 'employee.delete', 'name' => 'Delete Employee', 'module' => 'employees'],
            
            // Attendance
            ['key' => 'attendance.view', 'name' => 'View Attendance', 'module' => 'attendance'],
            ['key' => 'attendance.manage', 'name' => 'Manage Attendance', 'module' => 'attendance'],

            // Payroll
            ['key' => 'salary.view', 'name' => 'View Salary', 'module' => 'payroll'],
            ['key' => 'salary.calculate', 'name' => 'Calculate Salary', 'module' => 'payroll'],

            // Finance
            ['key' => 'expense.view', 'name' => 'View Expenses', 'module' => 'finance'],
            ['key' => 'expense.create', 'name' => 'Create Expense', 'module' => 'finance'],
            ['key' => 'expense.edit', 'name' => 'Edit Expense', 'module' => 'finance'],
            ['key' => 'expense.delete', 'name' => 'Delete Expense', 'module' => 'finance'],
            ['key' => 'bank_account.view', 'name' => 'View Bank Accounts', 'module' => 'finance'],
            ['key' => 'bank_account.create', 'name' => 'Create Bank Account', 'module' => 'finance'],
            ['key' => 'bank_account.edit', 'name' => 'Edit Bank Account', 'module' => 'finance'],
            ['key' => 'bank_account.delete', 'name' => 'Delete Bank Account', 'module' => 'finance'],
        ];

        $permModel = $this->db->table('permissions');

        foreach ($permissions as $perm) {
            if (!isset($mods[$perm['module']])) continue;
            
            if ($permModel->where('permission_key', $perm['key'])->countAllResults() === 0) {
                $permModel->insert([
                    'permission_key'  => $perm['key'],
                    'permission_name' => $perm['name'],
                    'module_id'       => $mods[$perm['module']],
                ]);
            }
        }
    }
}
