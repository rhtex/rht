<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReferenceNumberToLedgerTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'reference_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'payment_method'
            ],
        ];

        $this->forge->addColumn('weaver_ledger_transactions', $fields);
        $this->forge->addColumn('loom_ledger_transactions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('weaver_ledger_transactions', 'reference_number');
        $this->forge->dropColumn('loom_ledger_transactions', 'reference_number');
    }
}
