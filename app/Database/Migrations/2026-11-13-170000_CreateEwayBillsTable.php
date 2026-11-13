<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEwayBillsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('eway_bills')) {
            $db->query("
                CREATE TABLE `eway_bills` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `document_type` ENUM('invoice', 'credit_note', 'delivery_challan', 'purchase_bill') NOT NULL DEFAULT 'invoice',
                    `document_id` BIGINT UNSIGNED NOT NULL,
                    `document_number` VARCHAR(100) NOT NULL,
                    `document_date` DATE NOT NULL,
                    `zoho_ewaybill_id` VARCHAR(64) NULL,
                    `ewaybill_number` VARCHAR(50) NULL,
                    `ewaybill_date` DATETIME NULL,
                    `valid_until` DATETIME NULL,
                    `status` ENUM('draft', 'generated', 'part_a_generated', 'cancelled', 'expired', 'failed') NOT NULL DEFAULT 'draft',
                    `supply_type` VARCHAR(20) NOT NULL DEFAULT 'Outward',
                    `sub_supply_type` VARCHAR(50) NOT NULL DEFAULT 'Supply',
                    `transaction_type` VARCHAR(50) NOT NULL DEFAULT 'Regular',
                    `transport_mode` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Road, 2=Rail, 3=Air, 4=Ship',
                    `distance_km` INT UNSIGNED NOT NULL DEFAULT 0,
                    `transporter_id` VARCHAR(50) NULL COMMENT 'Transporter GSTIN / TRANSIN',
                    `transporter_name` VARCHAR(255) NULL,
                    `transporter_doc_number` VARCHAR(100) NULL COMMENT 'LR / RR / Airway Bill No',
                    `transporter_doc_date` DATE NULL,
                    `vehicle_number` VARCHAR(50) NULL,
                    `vehicle_type` VARCHAR(20) NOT NULL DEFAULT 'Regular' COMMENT 'Regular / Over Dimensional Cargo',
                    `from_place` VARCHAR(100) NULL,
                    `from_state_code` VARCHAR(10) NULL,
                    `from_pincode` VARCHAR(20) NULL,
                    `to_place` VARCHAR(100) NULL,
                    `to_state_code` VARCHAR(10) NULL,
                    `to_pincode` VARCHAR(20) NULL,
                    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `cess_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `cancel_reason_code` VARCHAR(10) NULL COMMENT '1=Duplicate, 2=Order Cancelled, 3=Data Error, 4=Other',
                    `cancel_remarks` TEXT NULL,
                    `cancelled_at` DATETIME NULL,
                    `last_sync_at` DATETIME NULL,
                    `response_payload` LONGTEXT NULL,
                    `error_message` TEXT NULL,
                    `created_by` INT UNSIGNED NULL,
                    `updated_by` INT UNSIGNED NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_ewb_doc` (`document_type`, `document_id`),
                    KEY `idx_ewb_number` (`ewaybill_number`),
                    KEY `idx_ewb_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down()
    {
        $this->forge->dropTable('eway_bills', true);
    }
}

