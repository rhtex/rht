ALTER TABLE `bank_accounts` ADD COLUMN `account_type` ENUM('Bank','Cash') DEFAULT 'Bank' AFTER `branch_name`;
ALTER TABLE `bank_transactions` ADD COLUMN `is_reconciled` BOOLEAN DEFAULT FALSE AFTER `balance_after`;
ALTER TABLE `bank_transactions` ADD COLUMN `reference_type` VARCHAR(50) NULL AFTER `is_reconciled`;
ALTER TABLE `bank_transactions` ADD COLUMN `reference_id` INT(11) UNSIGNED NULL AFTER `reference_type`;
ALTER TABLE `invoice_payments` ADD COLUMN `bank_transaction_id` INT(11) UNSIGNED NULL AFTER `bank_account_id`;
ALTER TABLE `expenses` ADD COLUMN `bank_transaction_id` INT(11) UNSIGNED NULL AFTER `bank_account_id`;
ALTER TABLE `agent_payments` ADD COLUMN `bank_transaction_id` INT(11) UNSIGNED NULL AFTER `bank_account_id`;

-- Add foreign keys
ALTER TABLE `invoice_payments` ADD CONSTRAINT `invoice_payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `expenses` ADD CONSTRAINT `expenses_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `agent_payments` ADD CONSTRAINT `agent_payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Create default cash account
INSERT INTO `bank_accounts` (`bank_name`, `account_number`, `ifsc_code`, `branch_name`, `account_type`, `current_balance`, `status`, `created_at`, `updated_at`)
SELECT 'Cash in Hand', 'CASH-001', 'N/A', 'Cash Account', 'Cash', 0.00, 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `bank_accounts` WHERE `account_number` = 'CASH-001');
