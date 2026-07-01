<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClearAllDCRelatedRecords extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Disable foreign keys checks
        $db->query("SET FOREIGN_KEY_CHECKS = 0;");
        
        // 1. Truncate Dyeing Tables
        $db->query("TRUNCATE TABLE production_yarn_dyeing_receipt_items;");
        $db->query("TRUNCATE TABLE production_yarn_dyeing_receipts;");
        $db->query("TRUNCATE TABLE production_yarn_dyeing_dc_items;");
        $db->query("TRUNCATE TABLE production_yarn_dyeing_dcs;");
        
        // 2. Truncate Twisting Tables
        $db->query("TRUNCATE TABLE production_yarn_twisting_receipt_items;");
        $db->query("TRUNCATE TABLE production_yarn_twisting_receipts;");
        $db->query("TRUNCATE TABLE production_yarn_twisting_dc_items;");
        $db->query("TRUNCATE TABLE production_yarn_twisting_dcs;");

        // 3. Truncate Warping & Sizing Tables
        $db->query("TRUNCATE TABLE production_yarn_warping_sizing_receipt_items;");
        $db->query("TRUNCATE TABLE production_yarn_warping_sizing_receipts;");
        $db->query("TRUNCATE TABLE production_yarn_warping_sizing_dc_items;");
        $db->query("TRUNCATE TABLE production_yarn_warping_sizing_dc_beams;");
        $db->query("TRUNCATE TABLE production_yarn_warping_sizing_dcs;");

        // 4. Truncate Weaving Tables
        $db->query("TRUNCATE TABLE production_yarn_weaving_receipt_items;");
        $db->query("TRUNCATE TABLE production_yarn_weaving_receipts;");
        $db->query("TRUNCATE TABLE production_yarn_weaving_dc_items;");
        $db->query("TRUNCATE TABLE production_yarn_weaving_dcs;");

        // 5. Clean up Stock movements (Keep purchases but drop DC related movements: Issue_Job_Work and Receipt_Job_Work)
        $db->query("DELETE FROM production_yarn_stock_movements WHERE movement_type IN ('Issue_Job_Work', 'Receipt_Job_Work');");
        
        // Reset beam holds back to Empty/In House
        $db->query("UPDATE production_beams SET location = 'In House', current_holder = 'Main Office', status = 'Empty', remarks = NULL;");

        // Re-enable foreign key checks
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function down()
    {
        // No down operation needed
    }
}
