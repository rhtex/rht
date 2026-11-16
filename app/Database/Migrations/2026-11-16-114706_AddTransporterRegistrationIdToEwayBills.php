<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransporterRegistrationIdToEwayBills extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('transporter_registration_id', 'eway_bills')) {
            $this->forge->addColumn('eway_bills', [
                'transporter_registration_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'null'       => true,
                    'after'      => 'transporter_id',
                    'comment'    => 'Transporter GSTIN / TRANSIN',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('transporter_registration_id', 'eway_bills')) {
            $this->forge->dropColumn('eway_bills', 'transporter_registration_id');
        }
    }
}
