<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToVendorCreditsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Modify status column to support Draft, Open, Closed, Void, Unused, Partial, Used
        $db->query("ALTER TABLE `vendor_credits` MODIFY COLUMN `status` ENUM('Draft', 'Open', 'Closed', 'Void', 'Unused', 'Partial', 'Used') DEFAULT 'Open'");

        // 2. Add additional columns if they don't exist
        $fields = [
            'credit_note_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'vendor_id',
            ],
            'bill_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'credit_note_date',
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'used_amount',
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'created_by',
            ],
        ];

        $existingFields = $db->getFieldNames('vendor_credits');
        $toAdd = [];
        foreach ($fields as $col => $def) {
            if (!in_array($col, $existingFields, true)) {
                $toAdd[$col] = $def;
            }
        }

        if (!empty($toAdd)) {
            $this->forge->addColumn('vendor_credits', $toAdd);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $fields = ['credit_note_date', 'bill_id', 'balance', 'updated_by'];
        $existing = $db->getFieldNames('vendor_credits');
        $toDrop = array_intersect($fields, $existing);
        if (!empty($toDrop)) {
            $this->forge->dropColumn('vendor_credits', $toDrop);
        }
    }
}
