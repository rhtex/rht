<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZohoTransporterIdToTransports extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('zoho_transporter_id', 'transports')) {
            $this->forge->addColumn('transports', [
                'zoho_transporter_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'null'       => true,
                    'after'      => 'transport_code',
                    'comment'    => 'Zoho Books / Inventory Internal Transporter ID',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('zoho_transporter_id', 'transports')) {
            $this->forge->dropColumn('transports', 'zoho_transporter_id');
        }
    }
}
