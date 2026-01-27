<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentPaymentsTable extends Migration
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
            'payment_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'payment_date' => [
                'type' => 'DATE',
            ],
            'payment_mode' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash', 'Bank Transfer', 'Cheque', 'UPI', 'Other'],
                'default'    => 'Cash',
            ],
            'bank_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('agent_id');
        $this->forge->addKey('payment_date');
        $this->forge->addForeignKey('agent_id', 'agents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('bank_account_id', 'bank_accounts', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('agent_payments');
    }

    public function down()
    {
        $this->forge->dropTable('agent_payments');
    }
}
