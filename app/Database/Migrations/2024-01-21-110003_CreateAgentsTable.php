<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentsTable extends Migration
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
            'agent_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'address_proof_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'address_proof_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'address_proof_front' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'address_proof_back' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'address_1' => [
                'type'       => 'TEXT',
            ],
            'address_2' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'village' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'country_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
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
        $this->forge->addForeignKey('state_id', 'states', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('country_id', 'countries', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('agents');
    }

    public function down()
    {
        $this->forge->dropTable('agents');
    }
}
