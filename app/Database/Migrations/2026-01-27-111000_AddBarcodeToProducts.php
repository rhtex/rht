<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBarcodeToProducts extends Migration
{
    public function up()
    {
        $fields = [
            'barcode' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'product_name'
            ],
        ];
        $this->forge->addColumn('products', $fields);
        $this->forge->addUniqueKey('barcode');
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'barcode');
    }
}
