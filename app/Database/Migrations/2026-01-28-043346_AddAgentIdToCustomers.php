<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgentIdToCustomers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('agent_id', 'customers')) {
            $this->forge->addColumn('customers', [
                'agent_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'status' // Place it somewhere reasonable
                ],
            ]);
        }
        
        // Optional: Add Foreign Key
        // $this->forge->addForeignKey('agent_id', 'agents', 'id', 'SET NULL', 'CASCADE');
        // $this->forge->processIndexes('customers'); 
        // Note: Adding FK in addColumn might be tricky depending on driver, separate query or proper usage is better.
        // Let's stick to just the column for now to be safe with existing data, or use raw sql if needed.
        // Actually, simple column is fine.
    }

    public function down()
    {
        $this->forge->dropColumn('customers', 'agent_id');
    }
}
