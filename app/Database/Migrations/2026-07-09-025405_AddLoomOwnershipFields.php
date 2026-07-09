<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoomOwnershipFields extends Migration
{
    public function up()
    {
        $fields = [
            'loom_owner' => [
                'type' => 'ENUM',
                'constraint' => ['Company', 'Weaver'],
                'default' => 'Weaver',
                'after' => 'status'
            ],
            'loom_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'loom_owner'
            ],
            'jacquard_owner' => [
                'type' => 'ENUM',
                'constraint' => ['Company', 'Weaver'],
                'default' => 'Weaver',
                'after' => 'loom_cost'
            ],
            'jacquard_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'jacquard_owner'
            ],
            'fitted_by' => [
                'type' => 'ENUM',
                'constraint' => ['Company', 'Weaver'],
                'default' => 'Weaver',
                'after' => 'jacquard_cost'
            ],
            'fitting_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'fitted_by'
            ],
        ];

        $this->forge->addColumn('weaver_looms', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('weaver_looms', [
            'loom_owner',
            'loom_cost',
            'jacquard_owner',
            'jacquard_cost',
            'fitted_by',
            'fitting_cost'
        ]);
    }
}
