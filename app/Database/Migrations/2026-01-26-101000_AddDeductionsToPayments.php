<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeductionsToPayments extends Migration
{
    public function up()
    {
        $fields = [
            'discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'amount',
            ],
            'mahimai_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'discount_amount',
            ],
            'postal_charges' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'mahimai_amount',
            ],
        ];

        $this->forge->addColumn('payments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('payments', ['discount_amount', 'mahimai_amount', 'postal_charges']);
    }
}
