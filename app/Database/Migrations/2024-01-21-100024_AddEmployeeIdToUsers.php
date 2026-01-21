<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmployeeIdToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'employee_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'role_id',
            ],
        ]);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'SET NULL', 'CASCADE');
        // Redoing the table to apply foreign key if necessary, 
        // but column add with FK in one go might depend on DB engine.
        // In CI4, usually we use process() or just addColumn then addForeignKey.
    }

    public function down()
    {
        $this->forge->dropForeignKey('users', 'users_employee_id_foreign');
        $this->forge->dropColumn('users', 'employee_id');
    }
}
