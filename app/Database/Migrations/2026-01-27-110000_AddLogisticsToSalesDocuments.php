<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogisticsToSalesDocuments extends Migration
{
    public function up()
    {
        $fields = [
            'transport_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'reference_number'
            ],
            'waybill_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'transport_name'
            ],
        ];

        $this->forge->addColumn('invoices', $fields);
        $this->forge->addColumn('quotations', $fields);
        $this->forge->addColumn('sales_orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', ['transport_name', 'waybill_number']);
        $this->forge->dropColumn('quotations', ['transport_name', 'waybill_number']);
        $this->forge->dropColumn('sales_orders', ['transport_name', 'waybill_number']);
    }
}
