<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJobWorkVendorFields extends Migration
{
    public function up()
    {
        $fields = [
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'whatsapp_number' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            // Store multiple job work types as TEXT (comma‑separated or newline)
            'job_work_type' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('production_vendors', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('production_vendors', ['email', 'whatsapp_number', 'job_work_type']);
    }
}
?>
