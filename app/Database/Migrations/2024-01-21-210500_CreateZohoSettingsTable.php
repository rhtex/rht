<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateZohoSettingsTable extends Migration
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
            'client_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'client_secret' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'refresh_token' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'organization_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'access_token' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'token_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'api_base_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => 'https://books.zoho.in/api/v3',
            ],
            'accounts_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'default'    => 'https://accounts.zoho.in/oauth/v2/token',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('zoho_settings');

        // Insert default row
        $db = \Config\Database::connect();
        $db->table('zoho_settings')->insert(['id' => 1]);
    }

    public function down()
    {
        $this->forge->dropTable('zoho_settings');
    }
}
