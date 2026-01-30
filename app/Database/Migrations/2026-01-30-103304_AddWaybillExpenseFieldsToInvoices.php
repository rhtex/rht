<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaybillExpenseFieldsToInvoices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [
            'transport_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'after'      => 'waybill_image'
            ],
            'transport_pay_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Paid', 'To Pay'],
                'default'    => 'To Pay',
                'after'      => 'transport_amount'
            ],
            'waybill_shipping_charge' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'after'      => 'transport_pay_type'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', 'transport_amount');
        $this->forge->dropColumn('invoices', 'transport_pay_type');
        $this->forge->dropColumn('invoices', 'waybill_shipping_charge');
    }
}
