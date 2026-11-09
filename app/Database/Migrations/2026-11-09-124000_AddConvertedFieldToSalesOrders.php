<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConvertedFieldToSalesOrders extends Migration
{
    public function up()
    {
        // Add converted_to_invoice_id to sales_orders
        $fields = [];

        if (!$this->db->fieldExists('converted_to_invoice_id', 'sales_orders')) {
            $fields['converted_to_invoice_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('sales_orders', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('converted_to_invoice_id', 'sales_orders')) {
            $this->forge->dropColumn('sales_orders', ['converted_to_invoice_id']);
        }
    }
}

