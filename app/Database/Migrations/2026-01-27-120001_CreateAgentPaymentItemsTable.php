<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentPaymentItemsTable extends Migration
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
            'agent_payment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'commission_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
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
        $this->forge->addKey('agent_payment_id');
        $this->forge->addKey('invoice_id');
        $this->forge->addForeignKey('agent_payment_id', 'agent_payments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invoice_id', 'invoices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('agent_payment_items');
    }

    public function down()
    {
        $this->forge->dropTable('agent_payment_items');
    }
}
