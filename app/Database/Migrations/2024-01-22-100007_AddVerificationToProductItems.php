<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVerificationToProductItems extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('product_items', [
            'item_image' => [
                'name' => 'received_image',
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
        ]);
        
        $fields = [
            'verified_image' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'received_image'
            ],
            'is_approved' => [
                'type' => 'ENUM',
                'constraint' => ['Yes', 'No'],
                'default' => 'No',
                'after' => 'status'
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'is_approved'
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by'
            ],
        ];
        $this->forge->addColumn('product_items', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('product_items', ['verified_image', 'is_approved', 'approved_by', 'approved_at']);
        $this->forge->modifyColumn('product_items', [
            'received_image' => [
                'name' => 'item_image',
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
        ]);
    }
}
