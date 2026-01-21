<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateExistingAddresses extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Move Customer Addresses
        $customers = $db->table('customers')->get()->getResultArray();
        foreach ($customers as $customer) {
            // Billing Address
            if (!empty($customer['billing_address'])) {
                $db->table('addresses')->insert([
                    'owner_type'    => 'customer',
                    'owner_id'      => $customer['id'],
                    'address_type'  => 'billing',
                    'address_line1' => $customer['billing_address'],
                    'city'          => 'N/A', // Placeholders as legacy data was single field
                    'pincode'       => 'N/A',
                    'state_id'      => $customer['state_id'] ?? 1,
                    'is_active'     => 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
            // Shipping Address
            if (!empty($customer['shipping_address'])) {
                $db->table('addresses')->insert([
                    'owner_type'    => 'customer',
                    'owner_id'      => $customer['id'],
                    'address_type'  => 'shipping',
                    'address_line1' => $customer['shipping_address'],
                    'city'          => 'N/A',
                    'pincode'       => 'N/A',
                    'state_id'      => $customer['state_id'] ?? 1,
                    'is_active'     => 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 2. Move Vendor Addresses
        $vendors = $db->table('vendors')->get()->getResultArray();
        foreach ($vendors as $vendor) {
            if (!empty($vendor['address'])) {
                $db->table('addresses')->insert([
                    'owner_type'    => 'vendor',
                    'owner_id'      => $vendor['id'],
                    'address_type'  => 'billing', // Defaulting legacy vendor address to billing
                    'address_line1' => $vendor['address'],
                    'city'          => 'N/A',
                    'pincode'       => 'N/A',
                    'state_id'      => $vendor['state_id'] ?? 1,
                    'is_active'     => 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 3. Drop legacy columns
        $this->forge->dropColumn('customers', ['billing_address', 'shipping_address', 'state_id']);
        $this->forge->dropColumn('vendors', ['address', 'state_id']);
    }

    public function down()
    {
        // Adding columns back (simplified)
        $this->forge->addColumn('customers', [
            'billing_address'  => ['type' => 'TEXT', 'null' => true],
            'shipping_address' => ['type' => 'TEXT', 'null' => true],
            'state_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addColumn('vendors', [
            'address'  => ['type' => 'TEXT', 'null' => true],
            'state_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
    }
}
