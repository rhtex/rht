<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoanPaymentsTable extends Migration
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
            'loan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'salary_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'payment_date' => [
                'type' => 'DATE',
            ],
            'amount_paid' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'remaining_after_payment' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'payment_month' => [
                'type' => 'VARCHAR',
                'constraint' => 7, // YYYY-MM format
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('loan_id', 'loans', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('loan_payments');
    }

    public function down()
    {
        $this->forge->dropTable('loan_payments');
    }
}
