<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocTrackingToInvoices extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('doc_courier_name', 'invoices')) {
            $this->forge->addColumn('invoices', [
                'doc_courier_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null' => true,
                    'after'      => 'waybill_shipping_charge'
                ],
                'doc_tracking_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null' => true,
                    'after'      => 'doc_courier_name'
                ],
                'doc_dispatched_date' => [
                    'type'       => 'DATE',
                    'null' => true,
                    'after'      => 'doc_tracking_number'
                ],
                'doc_status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Pending', 'Dispatched', 'Delivered', 'Returned'],
                    'default'    => 'Pending',
                    'after'      => 'doc_dispatched_date'
                ],
                'doc_received_date' => [
                    'type'       => 'DATE',
                    'null' => true,
                    'after'      => 'doc_status'
                ]
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', [
            'doc_courier_name', 'doc_tracking_number', 'doc_dispatched_date', 
            'doc_status', 'doc_received_date'
        ]);
    }
}
