<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrintConfigurationsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('print_configurations')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'document_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'unique'     => true,
                ],
                'paper_size' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'A4',
                ],
                'orientation' => [
                    'type'       => 'ENUM',
                    'constraint' => ['portrait', 'landscape'],
                    'default'    => 'portrait',
                ],
                'font_family' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'default'    => 'Helvetica, Arial, sans-serif',
                ],
                'base_font_size' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 15,
                    'default'    => '10.5px',
                ],
                'config_json' => [
                    'type' => 'LONGTEXT',
                ],
                'updated_by' => [
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
            $this->forge->createTable('print_configurations');
        }
    }

    public function down()
    {
        $this->forge->dropTable('print_configurations', true);
    }
}
