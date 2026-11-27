<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixZohoStateCodes extends Migration
{
    public function up()
    {
        $map = [
            'TG' => 'TS',
            'UT' => 'UK',
            'OR' => 'OD',
            'CT' => 'CG',
        ];
        foreach ($map as $old => $new) {
            $this->db->table('states')->where('state_code', $old)->update(['state_code' => $new]);
        }
    }

    public function down()
    {
        $map = [
            'TS' => 'TG',
            'UK' => 'UT',
            'OD' => 'OR',
            'CG' => 'CT',
        ];
        foreach ($map as $old => $new) {
            $this->db->table('states')->where('state_code', $old)->update(['state_code' => $new]);
        }
    }
}
