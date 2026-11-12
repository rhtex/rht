<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransportTypeToTransports extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('transport_type', 'transports')) {
            $this->forge->addColumn('transports', [
                'transport_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Courier Service', 'Parcel Service'],
                    'default'    => 'Parcel Service',
                    'null'       => false,
                    'after'      => 'transport_name',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('transport_type', 'transports')) {
            $this->forge->dropColumn('transports', 'transport_type');
        }
    }
}
