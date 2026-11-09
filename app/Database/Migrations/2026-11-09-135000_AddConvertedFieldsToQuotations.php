<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConvertedFieldsToQuotations extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('converted_to_so_id', 'quotations')) {
            $fields['converted_to_so_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ];
        }

        if (!$this->db->fieldExists('converted_to_invoice_id', 'quotations')) {
            $fields['converted_to_invoice_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'converted_to_so_id',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('quotations', $fields);
        }
    }

    public function down()
    {
        $dropFields = [];
        if ($this->db->fieldExists('converted_to_invoice_id', 'quotations')) {
            $dropFields[] = 'converted_to_invoice_id';
        }
        if ($this->db->fieldExists('converted_to_so_id', 'quotations')) {
            $dropFields[] = 'converted_to_so_id';
        }

        if (!empty($dropFields)) {
            $this->forge->dropColumn('quotations', $dropFields);
        }
    }
}

