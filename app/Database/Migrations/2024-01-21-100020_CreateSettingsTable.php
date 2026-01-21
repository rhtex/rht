<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('setting_key', true);
        $this->forge->createTable('settings');

        // Optional: Pre-seed some default settings
        $db = \Config\Database::connect();
        $db->table('settings')->insertBatch([
            [
                'setting_key'   => 'app_name',
                'setting_value' => 'RasiDev HR',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'setting_key'   => 'org_name',
                'setting_value' => 'RasiDev Solutions',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'setting_key'   => 'org_address',
                'setting_value' => '123 Corporate Blvd, Business City',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'setting_key'   => 'org_contact',
                'setting_value' => '+91 98765 43210',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
