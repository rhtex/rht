<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccountTypeToBankAccounts extends Migration
{
    public function up()
    {
        // Add account_type field to bank_accounts table
        $fields = [
            'account_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Bank', 'Cash'],
                'default'    => 'Bank',
                'after'      => 'branch_name',
            ],
        ];
        
        $this->forge->addColumn('bank_accounts', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('bank_accounts', 'account_type');
    }
}
