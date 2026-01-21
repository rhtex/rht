<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaidStatusAndExemptionsToSalaries extends Migration
{
    public function up()
    {
        // Add is_paid column to track if salary has been paid
        $this->forge->addColumn('salaries', [
            'is_paid' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'net_salary',
            ],
            'paid_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'is_paid',
            ],
            'exempt_loan_deduction' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'If 1, skip loan deduction for this salary',
                'after' => 'paid_date',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('salaries', ['is_paid', 'paid_date', 'exempt_loan_deduction']);
    }
}
