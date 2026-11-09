<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSalesOrderAndSelectionTypeToInvoices extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('so_number', 'invoices')) {
            $fields['so_number'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'po_date'
            ];
        }

        if (!$this->db->fieldExists('so_date', 'invoices')) {
            $fields['so_date'] = [
                'type' => 'DATE',
                'null' => true,
                'after' => 'so_number'
            ];
        }

        if (!$this->db->fieldExists('selection_type', 'invoices')) {
            $fields['selection_type'] = [
                'type' => 'ENUM',
                'constraint' => ['Personal Selection', 'Company Selection'],
                'null' => true,
                'after' => 'so_date'
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('invoices', $fields);
        }
    }

    public function down()
    {
        $columnsToDrop = [];
        foreach (['selection_type', 'so_date', 'so_number'] as $column) {
            if ($this->db->fieldExists($column, 'invoices')) {
                $columnsToDrop[] = $column;
            }
        }

        if (!empty($columnsToDrop)) {
            $this->forge->dropColumn('invoices', $columnsToDrop);
        }
    }
}

