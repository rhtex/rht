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
            ['module_name' => 'Sales Management', 'module_slug' => 'sales'],
            ['module_name' => 'Purchase Management', 'module_slug' => 'purchases'],
            ['module_name' => 'Inventory Management', 'module_slug' => 'inventory'],
            ['module_name' => 'Master Data', 'module_slug' => 'master_data'],
            ['module_name' => 'System Settings', 'module_slug' => 'settings'],
            ['module_name' => 'Tax Management', 'module_slug' => 'tax'],
            ['module_name' => 'Production Management', 'module_slug' => 'production'],
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
            ['key' => 'role.view', 'name' => 'View Roles', 'module' => 'users'],
            ['key' => 'role.create', 'name' => 'Create Role', 'module' => 'users'],
            ['key' => 'role.edit', 'name' => 'Edit Role', 'module' => 'users'],
            ['key' => 'role.delete', 'name' => 'Delete Role', 'module' => 'users'],
            ['key' => 'module.view', 'name' => 'View Modules', 'module' => 'users'],
            ['key' => 'module.create', 'name' => 'Create Module', 'module' => 'users'],
            ['key' => 'module.edit', 'name' => 'Edit Module', 'module' => 'users'],
            ['key' => 'module.delete', 'name' => 'Delete Module', 'module' => 'users'],
            ['key' => 'permission.view', 'name' => 'View Permissions', 'module' => 'users'],
            ['key' => 'permission.create', 'name' => 'Create Permission', 'module' => 'users'],
            ['key' => 'permission.edit', 'name' => 'Edit Permission', 'module' => 'users'],
            ['key' => 'permission.delete', 'name' => 'Delete Permission', 'module' => 'users'],
            
            // Employee Module
            ['key' => 'employee.view', 'name' => 'View Employees', 'module' => 'employees'],
            ['key' => 'employee.create', 'name' => 'Create Employee', 'module' => 'employees'],
            ['key' => 'employee.edit', 'name' => 'Edit Employee', 'module' => 'employees'],
            ['key' => 'employee.delete', 'name' => 'Delete Employee', 'module' => 'employees'],
            
            // Attendance
            ['key' => 'attendance.view', 'name' => 'View Attendance', 'module' => 'attendance'],
            ['key' => 'attendance.create', 'name' => 'Create Attendance', 'module' => 'attendance'],
            ['key' => 'attendance.manage', 'name' => 'Manage Attendance', 'module' => 'attendance'],

            // Payroll
            ['key' => 'salary.view', 'name' => 'View Salary', 'module' => 'payroll'],
            ['key' => 'salary.create', 'name' => 'Create Salary', 'module' => 'payroll'],
            ['key' => 'salary.edit', 'name' => 'Edit Salary', 'module' => 'payroll'],
            ['key' => 'salary.delete', 'name' => 'Delete Salary', 'module' => 'payroll'],
            ['key' => 'salary.calculate', 'name' => 'Calculate Salary', 'module' => 'payroll'],
            ['key' => 'loan.view', 'name' => 'View Loans', 'module' => 'payroll'],
            ['key' => 'loan.create', 'name' => 'Create Loan', 'module' => 'payroll'],
            ['key' => 'loan.edit', 'name' => 'Edit Loan', 'module' => 'payroll'],
            ['key' => 'loan.delete', 'name' => 'Delete Loan', 'module' => 'payroll'],

            // Finance
            ['key' => 'expense.view', 'name' => 'View Expenses', 'module' => 'finance'],
            ['key' => 'expense.create', 'name' => 'Create Expense', 'module' => 'finance'],
            ['key' => 'expense.edit', 'name' => 'Edit Expense', 'module' => 'finance'],
            ['key' => 'expense.delete', 'name' => 'Delete Expense', 'module' => 'finance'],
            ['key' => 'bank_account.view', 'name' => 'View Bank Accounts', 'module' => 'finance'],
            ['key' => 'bank_account.create', 'name' => 'Create Bank Account', 'module' => 'finance'],
            ['key' => 'bank_account.edit', 'name' => 'Edit Bank Account', 'module' => 'finance'],
            ['key' => 'bank_account.delete', 'name' => 'Delete Bank Account', 'module' => 'finance'],

            // Sales (Customers)
            ['key' => 'customer.view', 'name' => 'View Customers', 'module' => 'sales'],
            ['key' => 'customer.create', 'name' => 'Create Customer', 'module' => 'sales'],
            ['key' => 'customer.edit', 'name' => 'Edit Customer', 'module' => 'sales'],
            ['key' => 'customer.delete', 'name' => 'Delete Customer', 'module' => 'sales'],

            // Purchases (Vendors, Bills)
            ['key' => 'vendor.view', 'name' => 'View Vendors', 'module' => 'purchases'],
            ['key' => 'vendor.create', 'name' => 'Create Vendor', 'module' => 'purchases'],
            ['key' => 'vendor.edit', 'name' => 'Edit Vendor', 'module' => 'purchases'],
            ['key' => 'vendor.delete', 'name' => 'Delete Vendor', 'module' => 'purchases'],
            ['key' => 'bill.view', 'name' => 'View Bills', 'module' => 'purchases'],
            ['key' => 'bill.create', 'name' => 'Create Bill', 'module' => 'purchases'],
            ['key' => 'bill.edit', 'name' => 'Edit Bill', 'module' => 'purchases'],
            ['key' => 'bill.delete', 'name' => 'Delete/Void Bill', 'module' => 'purchases'],

            // Inventory (Products, Categories)
            ['key' => 'product.view', 'name' => 'View Products', 'module' => 'inventory'],
            ['key' => 'product.create', 'name' => 'Create Product', 'module' => 'inventory'],
            ['key' => 'product.edit', 'name' => 'Edit Product', 'module' => 'inventory'],
            ['key' => 'product.delete', 'name' => 'Delete Product', 'module' => 'inventory'],
            ['key' => 'product_category.view', 'name' => 'View Categories', 'module' => 'inventory'],
            ['key' => 'product_category.create', 'name' => 'Create Category', 'module' => 'inventory'],
            ['key' => 'product_category.edit', 'name' => 'Edit Category', 'module' => 'inventory'],
            ['key' => 'product_category.delete', 'name' => 'Delete Category', 'module' => 'inventory'],

            // Master Data
            ['key' => 'country.view', 'name' => 'View Countries', 'module' => 'master_data'],
            ['key' => 'country.create', 'name' => 'Create Country', 'module' => 'master_data'],
            ['key' => 'country.edit', 'name' => 'Edit Country', 'module' => 'master_data'],
            ['key' => 'country.delete', 'name' => 'Delete Country', 'module' => 'master_data'],
            ['key' => 'state.view', 'name' => 'View States', 'module' => 'master_data'],
            ['key' => 'state.create', 'name' => 'Create State', 'module' => 'master_data'],
            ['key' => 'state.edit', 'name' => 'Edit State', 'module' => 'master_data'],
            ['key' => 'state.delete', 'name' => 'Delete State', 'module' => 'master_data'],
            ['key' => 'agent.view', 'name' => 'View Agents', 'module' => 'master_data'],
            ['key' => 'agent.create', 'name' => 'Create Agent', 'module' => 'master_data'],
            ['key' => 'agent.edit', 'name' => 'Edit Agent', 'module' => 'master_data'],
            ['key' => 'agent.delete', 'name' => 'Delete Agent', 'module' => 'master_data'],
            ['key' => 'transport.view', 'name' => 'View Transports', 'module' => 'master_data'],
            ['key' => 'transport.create', 'name' => 'Create Transport', 'module' => 'master_data'],
            ['key' => 'transport.edit', 'name' => 'Edit Transport', 'module' => 'master_data'],
            ['key' => 'transport.delete', 'name' => 'Delete Transport', 'module' => 'master_data'],

            // Settings
            ['key' => 'setting.view', 'name' => 'View Settings', 'module' => 'settings'],
            ['key' => 'setting.edit', 'name' => 'Edit Settings', 'module' => 'settings'],

            // Tax Permissions
            ['key' => 'tax.view', 'name' => 'View Taxes', 'module' => 'tax'],
            ['key' => 'tax.create', 'name' => 'Create Tax', 'module' => 'tax'],
            ['key' => 'tax.edit', 'name' => 'Edit Tax', 'module' => 'tax'],
            ['key' => 'tax.delete', 'name' => 'Delete Tax', 'module' => 'tax'],

            // Production Permissions
            ['key' => 'weaver.view', 'name' => 'View Weavers', 'module' => 'production'],
            ['key' => 'weaver.create', 'name' => 'Create Weaver', 'module' => 'production'],
            ['key' => 'weaver.edit', 'name' => 'Edit Weaver/Loom', 'module' => 'production'],
            ['key' => 'weaver.delete', 'name' => 'Delete Weaver/Loom', 'module' => 'production'],
            
            ['key' => 'warp_allocation.view', 'name' => 'View Warp & Weft Allocations', 'module' => 'production'],
            ['key' => 'warp_allocation.create', 'name' => 'Create Warp & Weft Allocation', 'module' => 'production'],
            ['key' => 'warp_allocation.edit', 'name' => 'Edit Warp & Weft Allocation', 'module' => 'production'],
            ['key' => 'warp_allocation.delete', 'name' => 'Delete Warp & Weft Allocation', 'module' => 'production'],
            
            ['key' => 'production_receipt.view', 'name' => 'View Production Receipts', 'module' => 'production'],
            ['key' => 'production_receipt.create', 'name' => 'Create Production Receipt', 'module' => 'production'],
            ['key' => 'production_receipt.edit', 'name' => 'Edit Production Receipt', 'module' => 'production'],
            ['key' => 'production_receipt.delete', 'name' => 'Delete Production Receipt', 'module' => 'production'],
            
            ['key' => 'yarn_beam.view', 'name' => 'View Yarn Beams', 'module' => 'production'],
            ['key' => 'yarn_beam.create', 'name' => 'Create Yarn Beam', 'module' => 'production'],
            ['key' => 'yarn_beam.delete', 'name' => 'Delete Yarn Beam', 'module' => 'production'],
            
            ['key' => 'yarn_inventory.view', 'name' => 'View Yarn Inventory', 'module' => 'production'],
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
