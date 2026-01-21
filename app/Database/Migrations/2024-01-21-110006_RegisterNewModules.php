<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterNewModules extends Migration
{
    public function up()
    {
        $modules = [
            ['name' => 'Countries', 'slug' => 'country', 'status' => 'active'],
            ['name' => 'States', 'slug' => 'state', 'status' => 'active'],
            ['name' => 'Agents', 'slug' => 'agent', 'status' => 'active'],
            ['name' => 'Transports', 'slug' => 'transport', 'status' => 'active'],
        ];

        foreach ($modules as $module) {
            // Check if exists
            $existing = $this->db->table('modules')->where('module_slug', $module['slug'])->get()->getRow();
            if (!$existing) {
                $this->db->table('modules')->insert([
                    'module_name' => $module['name'],
                    'module_slug' => $module['slug'],
                    'status'      => $module['status'],
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function down()
    {
        // Optional: Remove modules and permissions
    }
}
