<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaybillImageToInvoices extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('waybill_image', 'invoices')) {
            $this->forge->addColumn('invoices', [
                'waybill_image' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'ewaybill_number'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', 'waybill_image');
    }
}
