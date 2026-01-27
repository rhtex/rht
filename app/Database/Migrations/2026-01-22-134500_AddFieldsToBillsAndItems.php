<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToBillsAndItems extends Migration
{
    public function up()
    {
        // Columns already added in 2026-01-22-131500_AddDiscountShippingToBills
        // Adding tax_id to bill_items below.

        // Add tax_id to bill_items table
        $itemFields = [
            'tax_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'rate'
            ]
        ];
        $this->forge->addColumn('bill_items', $itemFields);
        
        $this->forge->addForeignKey('tax_id', 'taxes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('bill_items');
    }

    public function down()
    {
        $this->forge->dropColumn('bills', ['discount_amount', 'discount_type', 'shipping_charge', 'roundoff_amount']);
        $this->forge->dropForeignKey('bill_items', 'bill_items_tax_id_foreign');
        $this->forge->dropColumn('bill_items', 'tax_id');
    }
}
