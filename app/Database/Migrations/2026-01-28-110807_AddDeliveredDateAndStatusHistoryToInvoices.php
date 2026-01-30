<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveredDateAndStatusHistoryToInvoices extends Migration
{
    public function up()
    {
        // Add delivered_date to invoices table
        $this->forge->addColumn('invoices', [
            'delivered_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'delivery_status'
            ]
        ]);

        // Create invoice_status_history table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('invoice_id', 'invoices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('invoice_status_history');
    }

    public function down()
    {
        $this->forge->dropTable('invoice_status_history');
        $this->forge->dropColumn('invoices', 'delivered_date');
    }
}
