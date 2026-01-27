<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPricingToProducts extends Migration
{
    public function up()
    {
        $fields = [
            'selling_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'unit'
            ],
            'tax_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'selling_price'
            ],
        ];

        $this->forge->addColumn('products', $fields);
        $this->forge->addForeignKey('tax_id', 'taxes', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        // Drop foreign key first if supported by the driver, though MySQL often handles it or errors if not dropped first.
        // Forge doesn't have a direct dropForeignKey method in all CI4 versions, so column drop usually suffices in dev.
        $this->forge->dropColumn('products', ['selling_price', 'tax_id']);
    }
}
