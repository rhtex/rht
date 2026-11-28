<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReturnCopyToSalesReturns extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('return_copy', 'sales_returns')) {
            $this->forge->addColumn('sales_returns', [
                'return_copy' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'reason',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('return_copy', 'sales_returns')) {
            $this->forge->dropColumn('sales_returns', 'return_copy');
        }
    }
}
