<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBalesCountToInvoicesAndReturnShipments extends Migration
{
    public function up()
    {
        // 1. Invoices table
        if (!$this->db->fieldExists('bales_count', 'invoices')) {
            $this->forge->addColumn('invoices', [
                'bales_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'packages_count',
                ]
            ]);
        }

        // 2. Return Shipments table (Purchase Return Shipment)
        if (!$this->db->fieldExists('bales_count', 'return_shipments')) {
            $this->forge->addColumn('return_shipments', [
                'bales_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'packages_count',
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('bales_count', 'invoices')) {
            $this->forge->dropColumn('invoices', 'bales_count');
        }

        if ($this->db->fieldExists('bales_count', 'return_shipments')) {
            $this->forge->dropColumn('return_shipments', 'bales_count');
        }
    }
}

