<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWorkingHoursToEmployeesAndAttendance extends Migration
{
    public function up()
    {
        // Add daily_working_hours to employees
        $this->forge->addColumn('employees', [
            'daily_working_hours' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,2',
                'default'    => 8.00,
                'after'      => 'basic_salary',
            ],
        ]);

        // Add tracking columns to attendance
        $this->forge->addColumn('attendance', [
            'hours_worked' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'check_out_time',
            ],
            'shortfall_hours' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'hours_worked',
            ],
            'is_recovered' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'shortfall_hours',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'daily_working_hours');
        $this->forge->dropColumn('attendance', ['hours_worked', 'shortfall_hours', 'is_recovered']);
    }
}
