<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalariesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employee_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'salary_month' => [
                'type' => 'DATE', // Using DATE to store first day of month, e.g., 2024-01-01
            ],
            'basic_salary' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'allowances' => [
                'type'       => 'TEXT', // Store JSON or detailed breakdown
                'null'       => true,
            ],
            'deductions' => [
                'type'       => 'TEXT', // Store JSON
                'null'       => true,
            ],
            'net_salary' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('salaries');
    }

    public function down()
    {
        $this->forge->dropTable('salaries');
    }
}
