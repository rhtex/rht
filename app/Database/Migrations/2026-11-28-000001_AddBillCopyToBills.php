<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillCopyToBills extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('bill_copy', 'bills')) {
            $this->forge->addColumn('bills', [
                'bill_copy' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'reference_number',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('bill_copy', 'bills')) {
            $this->forge->dropColumn('bills', 'bill_copy');
        }
    }
}
