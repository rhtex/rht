<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZohoFields extends Migration
{
    public function up()
    {
        $fields = [
            'zoho_contact_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'id'
            ],
            'zoho_sync_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('customers', $fields);
        $this->forge->addColumn('vendors', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('customers', ['zoho_contact_id', 'zoho_sync_at']);
        $this->forge->dropColumn('vendors', ['zoho_contact_id', 'zoho_sync_at']);
    }
}
