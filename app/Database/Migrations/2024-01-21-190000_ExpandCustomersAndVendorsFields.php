<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandCustomersAndVendorsFields extends Migration
{
    public function up()
    {
        $fields = [
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'after'      => 'email'
            ],
            'whatsapp_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'phone'
            ],
            'opening_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'pan_number'
            ],
            'balance_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Dr', 'Cr'],
                'default'    => 'Dr',
                'after'      => 'opening_balance'
            ],
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
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('customers', array_merge($fields, [
            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'balance_type'
            ],
            'credit_period_days' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
                'after'      => 'credit_limit'
            ]
        ]));

        $this->forge->addColumn('vendors', $fields);
    }

    public function down()
    {
        // No down needed for now as it's an expansion
    }
}
