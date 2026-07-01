<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConesToWarpingSizingJobWork extends Migration
{
    public function up()
    {
        // Add cones fields to production_yarn_warping_sizing_dc_items
        $this->forge->addColumn('production_yarn_warping_sizing_dc_items', [
            'cones_issued' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantity_issued_kg'
            ],
            'cones_received' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantity_received_kg'
            ],
            'cones_used' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantity_wastage_kg'
            ]
        ]);

        // Add cones fields to production_yarn_warping_sizing_receipt_items
        $this->forge->addColumn('production_yarn_warping_sizing_receipt_items', [
            'cones_received' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantity_received_kg'
            ],
            'cones_used' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'quantity_wastage_kg'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('production_yarn_warping_sizing_dc_items', ['cones_issued', 'cones_received', 'cones_used']);
        $this->forge->dropColumn('production_yarn_warping_sizing_receipt_items', ['cones_received', 'cones_used']);
    }
}
