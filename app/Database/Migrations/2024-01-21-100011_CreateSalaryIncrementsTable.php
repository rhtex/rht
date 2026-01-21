<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalaryIncrementsTable extends Migration
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
            'old_salary' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'new_salary' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'effective_date' => [
                'type' => 'DATE',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('salary_increments');
    }

    public function down()
    {
        $this->forge->dropTable('salary_increments');
    }
}
