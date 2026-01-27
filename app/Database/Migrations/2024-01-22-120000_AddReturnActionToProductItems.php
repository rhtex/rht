<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReturnActionToProductItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('product_items', [
            'return_action' => [
                'type'       => 'ENUM',
                'constraint' => ['Pending', 'Returned', 'Exchanged'],
                'default'    => 'Pending',
                'after'      => 'rejection_image'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('product_items', 'return_action');
    }
}
