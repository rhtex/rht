<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountShippingToBills extends Migration
{
    public function up()
    {
        $fields = [
            'discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'subtotal',
            ],
            'discount_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Amount', 'Percentage'],
                'default'    => 'Amount',
                'after'      => 'discount_amount',
            ],
            'shipping_charge' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'discount_type',
            ],
            'roundoff_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'shipping_charge',
            ],
        ];

        $this->forge->addColumn('bills', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('bills', ['discount_amount', 'discount_type', 'shipping_charge', 'roundoff_amount']);
    }
}
