<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterLoomLedgerTransactionType extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->query("ALTER TABLE `loom_ledger_transactions` MODIFY COLUMN `transaction_type` ENUM('Principal', 'Interest Addition', 'Payment', 'Reversal', 'Waiveoff') NOT NULL DEFAULT 'Payment'");
    }

    public function down()
    {
        // Reverting this is difficult if Waiveoff data exists, but we define the base ENUM
        $db = \Config\Database::connect();
        // Option to just restore the previous ENUM
        $db->query("ALTER TABLE `loom_ledger_transactions` MODIFY COLUMN `transaction_type` ENUM('Principal', 'Interest Addition', 'Payment', 'Reversal') NOT NULL DEFAULT 'Payment'");
    }
}
