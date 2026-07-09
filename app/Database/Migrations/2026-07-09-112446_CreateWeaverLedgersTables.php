<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeaverLedgersTables extends Migration
{
    public function up()
    {
        // Table: weaver_ledgers
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'weaver_id' => [
                'type'       => 'INT',
                'constraint' => 11, // INT(11) in weavers table
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'e.g. Festival Advance, Personal Loan',
            ],
            'principal_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'interest_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => '0.00',
                'comment'    => 'Flat percentage',
            ],
            'total_amount_due' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'balance_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Active', 'Cleared'],
                'default'    => 'Active',
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
        // Note: weavers table id is INT(11) signed based on previous investigation
        $this->forge->addForeignKey('weaver_id', 'weavers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('weaver_ledgers', true);

        // Table: weaver_ledger_transactions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ledger_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'transaction_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Principal', 'Interest Addition', 'Payment', 'Reversal', 'Waiveoff'],
                'default'    => 'Payment',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'e.g., Cash, Bank Transfer, Auto Deduction',
            ],
            'transaction_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
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
        $this->forge->addForeignKey('ledger_id', 'weaver_ledgers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('weaver_ledger_transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('weaver_ledger_transactions', true);
        $this->forge->dropTable('weaver_ledgers', true);
    }
}
