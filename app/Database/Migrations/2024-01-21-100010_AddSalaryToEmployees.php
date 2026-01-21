<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSalaryToEmployees extends Migration
{
    public function up()
    {
        $fields = [
            'basic_salary' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'status'
            ],
        ];
        $this->forge->addColumn('employees', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'basic_salary');
    }
}
