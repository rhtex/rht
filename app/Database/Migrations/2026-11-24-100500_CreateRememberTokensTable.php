<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRememberTokensTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('user_remember_tokens')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'selector' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'unique'     => true,
                ],
                'validator_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->createTable('user_remember_tokens');
        }
    }

    public function down()
    {
        $this->forge->dropTable('user_remember_tokens', true);
    }
}
