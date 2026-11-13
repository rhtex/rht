<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogFileToDbRestores extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('log_file', 'db_restores')) {
            $fields = [
                'log_file' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'error_message',
                ],
            ];
            $this->forge->addColumn('db_restores', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('db_restores', 'log_file');
    }
}
