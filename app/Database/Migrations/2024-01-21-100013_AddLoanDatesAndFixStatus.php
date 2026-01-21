<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoanDatesAndFixStatus extends Migration
{
    public function up()
    {
        // Add processed_date and credited_date columns
        $this->forge->addColumn('loans', [
            'processed_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'loan_date',
            ],
            'credited_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'processed_date',
            ],
        ]);
        
        // Update status enum to include 'Approved' and 'Completed'
        $this->db->query("ALTER TABLE `loans` MODIFY `status` ENUM('Pending', 'Approved', 'active', 'Completed', 'paid', 'cancelled') DEFAULT 'Pending'");
    }

    public function down()
    {
        $this->forge->dropColumn('loans', ['processed_date', 'credited_date']);
        $this->db->query("ALTER TABLE `loans` MODIFY `status` ENUM('active', 'paid', 'cancelled') DEFAULT 'active'");
    }
}
