<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryStatusToInvoices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [
            'delivery_status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'In Transit', 'Delivered', 'Cancelled'],
                'default' => 'Pending',
                'after' => 'waybill_image'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', 'delivery_status');
    }
}
