<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreditStatusToLoans extends Migration
{
    public function up()
    {
        $this->forge->addColumn('loans', [
            'credit_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Pending', 'Credited'],
                'default'    => 'Pending',
                'after'      => 'credited_date',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('loans', 'credit_status');
    }
}
