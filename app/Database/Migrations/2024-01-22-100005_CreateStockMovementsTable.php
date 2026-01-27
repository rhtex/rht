<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockMovementsTable extends Migration
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
            'product_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['in', 'out'],
            ],
            'reason' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('stock_movements');
    }

    public function down()
    {
        $this->forge->dropTable('stock_movements');
    }
}
