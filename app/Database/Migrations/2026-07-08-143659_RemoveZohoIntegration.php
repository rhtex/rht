<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveZohoIntegration extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Drop columns from customers
        if ($this->db->fieldExists('zoho_contact_id', 'customers')) {
            $this->forge->dropColumn('customers', 'zoho_contact_id');
        }
        if ($this->db->fieldExists('zoho_sync_at', 'customers')) {
            $this->forge->dropColumn('customers', 'zoho_sync_at');
        }

        // Drop columns from vendors
        if ($this->db->fieldExists('zoho_contact_id', 'vendors')) {
            $this->forge->dropColumn('vendors', 'zoho_contact_id');
        }
        if ($this->db->fieldExists('zoho_sync_at', 'vendors')) {
            $this->forge->dropColumn('vendors', 'zoho_sync_at');
        }

        // Drop zoho settings table if exists
        $this->forge->dropTable('zoho_settings', true);

        $tablesWithZohoColumns = [
            'quotations' => ['zoho_estimate_id', 'zoho_sync_status', 'zoho_sync_at'],
            'sales_orders' => ['zoho_salesorder_id', 'zoho_sync_status', 'zoho_sync_at'],
            'sales_returns' => ['zoho_credit_note_id', 'zoho_sync_status', 'zoho_sync_at'],
            'bills' => ['zoho_bill_id', 'zoho_sync_status', 'zoho_sync_at'],
            'payments' => ['zoho_payment_id', 'zoho_sync_status', 'zoho_sync_at'],
            'invoices' => ['zoho_invoice_id', 'zoho_sync_status', 'zoho_sync_at'],
            'invoice_payments' => ['zoho_payment_id', 'zoho_sync_status', 'zoho_sync_at']
        ];

        foreach ($tablesWithZohoColumns as $table => $columns) {
            foreach ($columns as $column) {
                if ($this->db->fieldExists($column, $table)) {
                    $this->forge->dropColumn($table, $column);
                }
            }
        }

        // Remove Zoho module if exists
        $module = $db->table('modules')->where('module_slug', 'zoho_settings')->get()->getRow();
        if ($module) {
            $db->table('role_permissions')->whereIn('permission_id', function ($builder) use ($module) {
                return $builder->select('id')->from('permissions')->where('module_id', $module->id);
            })->delete();
            $db->table('permissions')->where('module_id', $module->id)->delete();
            $db->table('modules')->where('id', $module->id)->delete();
        }

        // Clean up any remaining zoho permissions
        $db->table('permissions')->like('permission_key', 'zoho%')->delete();
    }

    public function down()
    {
        // No down migration
    }
}
