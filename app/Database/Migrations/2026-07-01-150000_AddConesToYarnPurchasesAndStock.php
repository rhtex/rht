<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConesToYarnPurchasesAndStock extends Migration
{
    public function up()
    {
        // Add cones parameter to production_yarn_purchases table
        $this->forge->addColumn('production_yarn_purchases', [
            'number_cones' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'number_bags'
            ]
        ]);

        // Add cones parameter to production_yarn_stock_movements table
        $this->forge->addColumn('production_yarn_stock_movements', [
            'quantity_cones' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantity_kg'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('production_yarn_purchases', ['number_cones']);
        $this->forge->dropColumn('production_yarn_stock_movements', ['quantity_cones']);
    }
}
