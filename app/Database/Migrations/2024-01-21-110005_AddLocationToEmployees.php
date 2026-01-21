<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationToEmployees extends Migration
{
    public function up()
    {
        $this->forge->addColumn('employees', [
            'state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'address_line_2',
            ],
            'country_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'state_id',
            ],
        ]);
        $this->forge->addForeignKey('state_id', 'states', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('country_id', 'countries', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('employees', 'employees_state_id_foreign');
        $this->forge->dropForeignKey('employees', 'employees_country_id_foreign');
        $this->forge->dropColumn('employees', ['state_id', 'country_id']);
    }
}
