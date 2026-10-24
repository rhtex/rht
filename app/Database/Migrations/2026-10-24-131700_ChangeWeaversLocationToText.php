<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeWeaversLocationToText extends Migration
{
    public function up()
    {
        $fields = [
            'location' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('weavers', $fields);
    }

    public function down()
    {
        $fields = [
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('weavers', $fields);
    }
}
