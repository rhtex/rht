<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterFinanceModules extends Migration
{
    public function up()
    {
        $modules = [
            ['name' => 'Expenses', 'slug' => 'expense', 'status' => 'active'],
            ['name' => 'Bank Accounts', 'slug' => 'bank_account', 'status' => 'active'],
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
    }
}
