<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSurplusHoursToAttendance extends Migration
{
    public function up()
    {
        $this->forge->addColumn('attendance', [
            'surplus_hours' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'shortfall_hours'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance', 'surplus_hours');
    }
}
