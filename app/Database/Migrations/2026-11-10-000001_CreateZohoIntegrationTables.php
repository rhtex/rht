<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateZohoIntegrationTables extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. zoho_settings table
        if (!$db->tableExists('zoho_settings')) {
            $db->query("
                CREATE TABLE `zoho_settings` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
                    `auto_sync_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                    `sync_mode` ENUM('immediate','queue_cron') NOT NULL DEFAULT 'immediate',
                    `data_center` VARCHAR(20) NOT NULL DEFAULT 'in',
                    `client_id_enc` TEXT NULL,
                    `client_secret_enc` TEXT NULL,
                    `refresh_token_enc` TEXT NULL,
                    `access_token_enc` TEXT NULL,
                    `token_expires_at` DATETIME NULL,
                    `organization_id` VARCHAR(64) NULL,
                    `organization_name` VARCHAR(255) NULL,
                    `organizations_cache_json` LONGTEXT NULL,
                    `oauth_redirect_url` VARCHAR(500) NULL,
                    `api_base_url` VARCHAR(255) NOT NULL DEFAULT 'https://www.zohoapis.in/books/v3',
                    `conflict_strategy` ENUM('manual_review','latest_timestamp','prefer_zoho','prefer_local') NOT NULL DEFAULT 'manual_review',
                    `preserve_document_numbers` TINYINT(1) NOT NULL DEFAULT 1,
                    `subscription_plan` VARCHAR(30) NOT NULL DEFAULT 'free',
                    `daily_api_limit` INT UNSIGNED NOT NULL DEFAULT 1000,
                    `per_minute_limit` INT UNSIGNED NOT NULL DEFAULT 90,
                    `max_concurrent_connections` INT UNSIGNED NOT NULL DEFAULT 5,
                    `finance_default_account_map_json` LONGTEXT NULL,
                    `last_connected_at` DATETIME NULL,
                    `last_sync_at` DATETIME NULL,
                    `last_error` TEXT NULL,
                    `updated_by` INT UNSIGNED NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $db->table('zoho_settings')->insert([
                'is_enabled'                 => 0,
                'auto_sync_enabled'          => 1,
                'sync_mode'                  => 'immediate',
                'data_center'                => 'in',
                'api_base_url'               => 'https://www.zohoapis.in/books/v3',
                'conflict_strategy'          => 'manual_review',
                'preserve_document_numbers'  => 1,
                'subscription_plan'          => 'free',
                'daily_api_limit'            => 1000,
                'per_minute_limit'           => 90,
                'max_concurrent_connections' => 5,
                'created_at'                 => date('Y-m-d H:i:s'),
                'updated_at'                 => date('Y-m-d H:i:s'),
            ]);
        } else {
            $cols = array_map('strtolower', $db->getFieldNames('zoho_settings'));
            if (!in_array('subscription_plan', $cols, true)) {
                $db->query("ALTER TABLE `zoho_settings` ADD COLUMN `subscription_plan` VARCHAR(30) NOT NULL DEFAULT 'free' AFTER `preserve_document_numbers`");
            }
            if (!in_array('daily_api_limit', $cols, true)) {
                $db->query("ALTER TABLE `zoho_settings` ADD COLUMN `daily_api_limit` INT UNSIGNED NOT NULL DEFAULT 1000 AFTER `subscription_plan`");
            }
            if (!in_array('per_minute_limit', $cols, true)) {
                $db->query("ALTER TABLE `zoho_settings` ADD COLUMN `per_minute_limit` INT UNSIGNED NOT NULL DEFAULT 90 AFTER `daily_api_limit`");
            }
            if (!in_array('max_concurrent_connections', $cols, true)) {
                $db->query("ALTER TABLE `zoho_settings` ADD COLUMN `max_concurrent_connections` INT UNSIGNED NOT NULL DEFAULT 5 AFTER `per_minute_limit`");
            }
        }

        // 2. zoho_sync_mapping table
        if (!$db->tableExists('zoho_sync_mapping')) {
            $db->query("
                CREATE TABLE `zoho_sync_mapping` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `module` VARCHAR(50) NOT NULL,
                    `local_record_id` BIGINT UNSIGNED NOT NULL,
                    `zoho_record_id` VARCHAR(64) NOT NULL,
                    `zoho_record_number` VARCHAR(100) NULL,
                    `last_local_sync_at` DATETIME NULL,
                    `last_zoho_sync_at` DATETIME NULL,
                    `last_successful_sync_at` DATETIME NULL,
                    `sync_direction` ENUM('TO_ZOHO','FROM_ZOHO','BIDIRECTIONAL') NOT NULL DEFAULT 'TO_ZOHO',
                    `sync_status` ENUM('SYNCED','PENDING','FAILED','CONFLICT','VOIDED','DELETED_IN_ZOHO','ARCHIVED') NOT NULL DEFAULT 'SYNCED',
                    `local_version_hash` CHAR(64) NULL,
                    `zoho_version_hash` CHAR(64) NULL,
                    `zoho_last_modified_time` VARCHAR(64) NULL,
                    `metadata_json` LONGTEXT NULL,
                    `last_error` TEXT NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uq_zoho_map_module_local` (`module`, `local_record_id`),
                    UNIQUE KEY `uq_zoho_map_module_zoho` (`module`, `zoho_record_id`),
                    KEY `idx_zoho_map_status` (`module`, `sync_status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 3. zoho_sync_queue table
        if (!$db->tableExists('zoho_sync_queue')) {
            $db->query("
                CREATE TABLE `zoho_sync_queue` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `batch_id` VARCHAR(64) NULL,
                    `module` VARCHAR(50) NOT NULL,
                    `local_record_id` BIGINT UNSIGNED NULL,
                    `zoho_record_id` VARCHAR(64) NULL,
                    `operation` ENUM('CREATE','UPDATE','DELETE','VOID','MARK_INACTIVE','SYNC_FROM_ZOHO') NOT NULL,
                    `direction` ENUM('TO_ZOHO','FROM_ZOHO') NOT NULL DEFAULT 'TO_ZOHO',
                    `source` ENUM('LOCAL','ZOHO','SYSTEM') NOT NULL DEFAULT 'LOCAL',
                    `priority` TINYINT UNSIGNED NOT NULL DEFAULT 5,
                    `payload_json` LONGTEXT NULL,
                    `status` ENUM('PENDING','PROCESSING','COMPLETED','FAILED','CONFLICT','CANCELLED') NOT NULL DEFAULT 'PENDING',
                    `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
                    `max_attempts` INT UNSIGNED NOT NULL DEFAULT 5,
                    `next_retry_at` DATETIME NULL,
                    `locked_at` DATETIME NULL,
                    `error_message` TEXT NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_zoho_queue_poll` (`status`, `next_retry_at`, `priority`, `id`),
                    KEY `idx_zoho_queue_record` (`module`, `local_record_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 4. zoho_sync_logs table
        if (!$db->tableExists('zoho_sync_logs')) {
            $db->query("
                CREATE TABLE `zoho_sync_logs` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `batch_id` VARCHAR(64) NULL,
                    `queue_id` BIGINT UNSIGNED NULL,
                    `module` VARCHAR(50) NOT NULL,
                    `local_record_id` BIGINT UNSIGNED NULL,
                    `zoho_record_id` VARCHAR(64) NULL,
                    `direction` ENUM('TO_ZOHO','FROM_ZOHO','OAUTH','SYSTEM') NOT NULL DEFAULT 'TO_ZOHO',
                    `operation` VARCHAR(50) NOT NULL,
                    `http_method` VARCHAR(10) NULL,
                    `endpoint` VARCHAR(500) NULL,
                    `request_payload` LONGTEXT NULL,
                    `response_code` INT NULL,
                    `response_payload` LONGTEXT NULL,
                    `duration_ms` INT UNSIGNED NULL,
                    `status` ENUM('SUCCESS','FAILED','WARNING','RATE_LIMITED') NOT NULL DEFAULT 'SUCCESS',
                    `error_message` TEXT NULL,
                    `created_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_zoho_logs_module` (`module`, `local_record_id`),
                    KEY `idx_zoho_logs_batch` (`batch_id`),
                    KEY `idx_zoho_logs_status` (`status`, `created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 5. zoho_sync_conflicts table
        if (!$db->tableExists('zoho_sync_conflicts')) {
            $db->query("
                CREATE TABLE `zoho_sync_conflicts` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `module` VARCHAR(50) NOT NULL,
                    `local_record_id` BIGINT UNSIGNED NOT NULL,
                    `zoho_record_id` VARCHAR(64) NOT NULL,
                    `record_number` VARCHAR(100) NULL,
                    `local_payload_json` LONGTEXT NOT NULL,
                    `zoho_payload_json` LONGTEXT NOT NULL,
                    `diff_fields_json` LONGTEXT NOT NULL,
                    `local_modified_at` DATETIME NULL,
                    `zoho_modified_at` DATETIME NULL,
                    `conflict_reason` VARCHAR(255) NOT NULL,
                    `status` ENUM('OPEN','RESOLVED_LOCAL','RESOLVED_ZOHO','IGNORED') NOT NULL DEFAULT 'OPEN',
                    `resolved_by` INT UNSIGNED NULL,
                    `resolved_at` DATETIME NULL,
                    `resolution_notes` TEXT NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_zoho_conflicts_status` (`status`, `module`),
                    KEY `idx_zoho_conflicts_record` (`module`, `local_record_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 6. zoho_sync_history table
        if (!$db->tableExists('zoho_sync_history')) {
            $db->query("
                CREATE TABLE `zoho_sync_history` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `batch_id` VARCHAR(64) NOT NULL,
                    `trigger_type` ENUM('AUTO','MANUAL_FROM_ZOHO','MANUAL_TO_ZOHO','FULL_BIDIRECTIONAL','MODULE_SYNC','RECORD_SYNC','RETRY_FAILED','RECONCILIATION','CLI_CRON') NOT NULL,
                    `module_filter` VARCHAR(100) NULL,
                    `total_processed` INT UNSIGNED NOT NULL DEFAULT 0,
                    `created_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `updated_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `deleted_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `skipped_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `conflict_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `status` ENUM('RUNNING','COMPLETED','PARTIAL','FAILED') NOT NULL DEFAULT 'RUNNING',
                    `started_at` DATETIME NOT NULL,
                    `completed_at` DATETIME NULL,
                    `initiated_by` INT UNSIGNED NULL,
                    `summary_json` LONGTEXT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uq_zoho_history_batch` (`batch_id`),
                    KEY `idx_zoho_history_started` (`started_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 7. zoho_sync_cursors table
        if (!$db->tableExists('zoho_sync_cursors')) {
            $db->query("
                CREATE TABLE `zoho_sync_cursors` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `module` VARCHAR(50) NOT NULL,
                    `last_zoho_modified_time` VARCHAR(64) NULL,
                    `last_local_sync_time` DATETIME NULL,
                    `last_page_processed` INT UNSIGNED NOT NULL DEFAULT 1,
                    `last_full_sync_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uq_zoho_cursor_module` (`module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 8. purchase_orders & purchase_order_items tables for full Purchase Order parity
        if (!$db->tableExists('purchase_orders')) {
            $db->query("
                CREATE TABLE `purchase_orders` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `vendor_id` INT UNSIGNED NOT NULL,
                    `purchase_order_number` VARCHAR(50) NOT NULL,
                    `order_date` DATE NOT NULL,
                    `delivery_date` DATE NULL,
                    `reference_number` VARCHAR(100) NULL,
                    `status` ENUM('Draft','Issued','Billed','Cancelled','Closed','Void') NOT NULL DEFAULT 'Draft',
                    `converted_to_bill_id` INT UNSIGNED NULL,
                    `is_inter_state` TINYINT(1) NOT NULL DEFAULT 0,
                    `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `discount_type` ENUM('Amount','Percentage','Fixed') NOT NULL DEFAULT 'Amount',
                    `shipping_charge` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `roundoff_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `cgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `sgst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `igst_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `notes` TEXT NULL,
                    `terms` TEXT NULL,
                    `created_at` DATETIME NULL,
                    `updated_at` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uq_purchase_order_number` (`purchase_order_number`),
                    KEY `idx_po_vendor` (`vendor_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!$db->tableExists('purchase_order_items')) {
            $db->query("
                CREATE TABLE `purchase_order_items` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `purchase_order_id` INT UNSIGNED NOT NULL,
                    `product_id` INT UNSIGNED NULL,
                    `description` TEXT NULL,
                    `hsn_code` VARCHAR(50) NULL,
                    `quantity` DECIMAL(15,2) NOT NULL DEFAULT 1.00,
                    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    `tax_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    `cgst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    `sgst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    `igst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    PRIMARY KEY (`id`),
                    KEY `idx_poi_po` (`purchase_order_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 9. Non-breaking enhancements for Customer Advances (invoice_payments) & Supplier Advances (payments)
        if ($db->tableExists('invoice_payments')) {
            $db->query("ALTER TABLE `invoice_payments` MODIFY `invoice_id` INT UNSIGNED NULL;");
            if (!$db->fieldExists('payment_type', 'invoice_payments')) {
                $db->query("ALTER TABLE `invoice_payments` ADD COLUMN `payment_type` ENUM('invoice_payment','customer_advance') NOT NULL DEFAULT 'invoice_payment' AFTER `customer_id`;");
            }
            if (!$db->fieldExists('unapplied_amount', 'invoice_payments')) {
                $db->query("ALTER TABLE `invoice_payments` ADD COLUMN `unapplied_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `amount`;");
            }
        }

        if ($db->tableExists('payments')) {
            $db->query("ALTER TABLE `payments` MODIFY `bill_id` INT UNSIGNED NULL;");
            if (!$db->fieldExists('payment_type', 'payments')) {
                $db->query("ALTER TABLE `payments` ADD COLUMN `payment_type` ENUM('bill_payment','supplier_advance') NOT NULL DEFAULT 'bill_payment' AFTER `vendor_id`;");
            }
            if (!$db->fieldExists('unapplied_amount', 'payments')) {
                $db->query("ALTER TABLE `payments` ADD COLUMN `unapplied_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `amount`;");
            }
        }

        // 10. Seed module & permissions if modules/permissions tables exist
        if ($db->tableExists('modules') && $db->tableExists('permissions')) {
            $existingMod = $db->table('modules')->where('module_slug', 'zoho_integration')->get()->getRowArray();
            if (!$existingMod) {
                $db->table('modules')->insert([
                    'module_name' => 'Zoho Books Integration',
                    'module_slug' => 'zoho_integration',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $modId = $db->insertID();
            } else {
                $modId = $existingMod['id'];
            }

            $perms = [
                ['permission_name' => 'View Zoho Integration', 'permission_key' => 'zoho.view'],
                ['permission_name' => 'Configure Zoho Integration', 'permission_key' => 'zoho.edit'],
                ['permission_name' => 'Run Zoho Synchronization', 'permission_key' => 'zoho.sync'],
            ];

            foreach ($perms as $p) {
                $existingPerm = $db->table('permissions')->where('permission_key', $p['permission_key'])->get()->getRowArray();
                if (!$existingPerm) {
                    $db->table('permissions')->insert([
                        'module_id'       => $modId,
                        'permission_name' => $p['permission_name'],
                        'permission_key'  => $p['permission_key'],
                        'created_at'      => date('Y-m-d H:i:s'),
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
                    $permId = $db->insertID();
                    if ($db->tableExists('role_permissions')) {
                        $db->table('role_permissions')->ignore(true)->insert([
                            'role_id'       => 1,
                            'permission_id' => $permId,
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('zoho_sync_cursors', true);
        $this->forge->dropTable('zoho_sync_history', true);
        $this->forge->dropTable('zoho_sync_conflicts', true);
        $this->forge->dropTable('zoho_sync_logs', true);
        $this->forge->dropTable('zoho_sync_queue', true);
        $this->forge->dropTable('zoho_sync_mapping', true);
        $this->forge->dropTable('zoho_settings', true);
    }
}

