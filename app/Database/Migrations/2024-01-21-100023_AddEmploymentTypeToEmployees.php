<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmploymentTypeToEmployees extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('employment_type', 'employees')) {
            $this->forge->addColumn('employees', [
                'employment_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Permanent', 'Temporary'],
                    'default'    => 'Permanent',
                    'after'      => 'status',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('employees', 'employment_type');
    }
}
