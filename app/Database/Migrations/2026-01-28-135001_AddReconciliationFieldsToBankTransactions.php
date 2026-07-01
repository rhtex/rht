<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReconciliationFieldsToBankTransactions extends Migration
{
    public function up()
    {
        // Add reconciliation fields to bank_transactions table
        $fields = [
            'is_reconciled' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'after'      => 'balance_after',
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'is_reconciled',
                'comment'    => 'Type: invoice_payment, payment, expense, agent_payment, etc.',
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'reference_type',
                'comment'    => 'ID of the referenced payment record',
            ],
        ];
        
        if (!$this->db->fieldExists('is_reconciled', 'bank_transactions')) {
            $this->forge->addColumn('bank_transactions', $fields);
        }
        
        // Add index for better query performance
        $this->forge->addKey(['reference_type', 'reference_id']);
    }

    public function down()
    {
        $this->forge->dropColumn('bank_transactions', ['is_reconciled', 'reference_type', 'reference_id']);
    }
}
