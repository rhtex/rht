<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUniqueBillNumberConstraint extends Migration
{
    public function up()
    {
        // Check if the unique key on bill_number exists, and drop it in favor of a standard index
        $db = \Config\Database::connect();
        
        // In MySQL, drop index bill_number if present
        $indexExists = $db->query("SHOW INDEX FROM bills WHERE Key_name = 'bill_number'")->getResultArray();
        if (!empty($indexExists)) {
            $db->query("ALTER TABLE bills DROP INDEX `bill_number`");
        }

        // Add standard non-unique index on bill_number if not already present
        $idxExists = $db->query("SHOW INDEX FROM bills WHERE Key_name = 'idx_bill_number'")->getResultArray();
        if (empty($idxExists)) {
            $db->query("ALTER TABLE bills ADD INDEX `idx_bill_number` (`bill_number`)");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $idxExists = $db->query("SHOW INDEX FROM bills WHERE Key_name = 'idx_bill_number'")->getResultArray();
        if (!empty($idxExists)) {
            $db->query("ALTER TABLE bills DROP INDEX `idx_bill_number`");
        }
        $db->query("ALTER TABLE bills ADD UNIQUE `bill_number` (`bill_number`)");
    }
}

