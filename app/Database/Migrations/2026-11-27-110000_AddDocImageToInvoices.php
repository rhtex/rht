<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocImageToInvoices extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('doc_image', 'invoices')) {
            $this->db->query("ALTER TABLE `invoices` ADD COLUMN `doc_image` VARCHAR(255) NULL AFTER `doc_dispatched_date`");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('doc_image', 'invoices')) {
            $this->db->query("ALTER TABLE `invoices` DROP COLUMN `doc_image`");
        }
    }
}
