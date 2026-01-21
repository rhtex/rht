<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoansTable extends Migration
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
            'loan_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'loan_date' => [
                'type' => 'DATE',
            ],
            'monthly_deduction' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'remaining_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'paid', 'cancelled'],
                'default'    => 'active',
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
        $this->forge->createTable('loans');
    }

    public function down()
    {
        $this->forge->dropTable('loans');
    }
}
