<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropCustomerBankFields extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('customers', ['bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch']);
    }

    public function down()
    {
        $fields = [
            'bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'bank_account_no' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'bank_ifsc' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'bank_branch' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('customers', $fields);
    }
}
