<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsInterStateToInvoices extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('is_inter_state', 'invoices')) {
            $this->forge->addColumn('invoices', [
                'is_inter_state' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'customer_id'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', 'is_inter_state');
    }
}
