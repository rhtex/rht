<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankTransactionIdToExpenses extends Migration
{
    public function up()
    {
        // Add bank_transaction_id field to expenses table
        $fields = [
            'bank_transaction_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'bank_account_id',
            ],
        ];
        
        if (!$this->db->fieldExists('bank_transaction_id', 'expenses')) {
            $this->forge->addColumn('expenses', $fields);
            
            // Add foreign key constraint
            $this->forge->addForeignKey('bank_transaction_id', 'bank_transactions', 'id', 'SET NULL', 'CASCADE', 'expenses');
        }
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('expenses', 'expenses_bank_transaction_id_foreign');
        
        // Drop column
        $this->forge->dropColumn('expenses', 'bank_transaction_id');
    }
}
