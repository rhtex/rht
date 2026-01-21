<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMultiplierToAttendance extends Migration
{
    public function up()
    {
        $this->forge->addColumn('attendance', [
            'multiplier' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,1',
                'default'    => 1.0,
                'after'      => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance', 'multiplier');
    }
}
