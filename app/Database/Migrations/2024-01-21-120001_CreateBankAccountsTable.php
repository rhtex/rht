<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBankAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'account_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'ifsc_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'branch_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('bank_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('bank_accounts');
    }
}
