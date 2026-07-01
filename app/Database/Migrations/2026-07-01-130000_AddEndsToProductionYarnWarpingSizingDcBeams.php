<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEndsToProductionYarnWarpingSizingDcBeams extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('ends', 'production_yarn_warping_sizing_dc_beams')) {
            $this->forge->addColumn('production_yarn_warping_sizing_dc_beams', [
                'ends' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'beam_number'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('ends', 'production_yarn_warping_sizing_dc_beams')) {
            $this->forge->dropColumn('production_yarn_warping_sizing_dc_beams', 'ends');
        }
    }
}
