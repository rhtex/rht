<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ForceAddLocationToEmployees extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if state_id exists
        if (!$db->fieldExists('state_id', 'employees')) {
            $this->forge->addColumn('employees', [
                'state_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'address_line_2',
                ],
            ]);
        }

        // Check if country_id exists
        if (!$db->fieldExists('country_id', 'employees')) {
            $this->forge->addColumn('employees', [
                'country_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'state_id',
                ],
            ]);
        }
        
        // Try to add foreign keys if they don't exist
        try {
            $this->forge->addForeignKey('state_id', 'states', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('country_id', 'countries', 'id', 'SET NULL', 'CASCADE');
        } catch (\Exception $e) {
            // Already exist or failed
        }
    }

    public function down()
    {
        // No down for force migration
    }
}
