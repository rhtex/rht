<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransportTable extends Migration
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
            'transport_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'transport_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'branch' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'branch_address' => [
                'type'       => 'TEXT',
            ],
            'branch_phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'branch_gst_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
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
        $this->forge->createTable('transports');
    }

    public function down()
    {
        $this->forge->dropTable('transports');
    }
}
