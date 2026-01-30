<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankTransactionIdToInvoicePayments extends Migration
{
    public function up()
    {
        // Add bank_transaction_id field to invoice_payments table
        $fields = [
            'bank_transaction_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'bank_account_id',
            ],
        ];
        
        $this->forge->addColumn('invoice_payments', $fields);
        
        // Add foreign key constraint
        $this->forge->addForeignKey('bank_transaction_id', 'bank_transactions', 'id', 'SET NULL', 'CASCADE', 'invoice_payments');
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('invoice_payments', 'invoice_payments_bank_transaction_id_foreign');
        
        // Drop column
        $this->forge->dropColumn('invoice_payments', 'bank_transaction_id');
    }
}
