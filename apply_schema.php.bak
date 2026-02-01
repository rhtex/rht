<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
$mysqli = new mysqli('localhost', 'root', '', 'rasidev_hr');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "Connected to database successfully.\n\n";

// Array of SQL statements
$sql_statements = [
    "ALTER TABLE `bank_accounts` ADD COLUMN `account_type` ENUM('Bank','Cash') DEFAULT 'Bank' AFTER `branch_name`",
    "ALTER TABLE `bank_transactions` ADD COLUMN `is_reconciled` BOOLEAN DEFAULT FALSE AFTER `balance_after`",
    "ALTER TABLE `bank_transactions` ADD COLUMN `reference_type` VARCHAR(50) NULL AFTER `is_reconciled`",
    "ALTER TABLE `bank_transactions` ADD COLUMN `reference_id` INT(11) UNSIGNED NULL AFTER `reference_type`",
    "ALTER TABLE `invoice_payments` ADD COLUMN `bank_transaction_id` INT(11) UNSIGNED NULL AFTER `bank_account_id`",
    "ALTER TABLE `expenses` ADD COLUMN `bank_transaction_id` INT(11) UNSIGNED NULL AFTER `bank_account_id`",
    "ALTER TABLE `agent_payments` ADD COLUMN `bank_transaction_id` INT(11) UNSIGNED NULL AFTER `bank_account_id`",
    "ALTER TABLE `invoice_payments` ADD CONSTRAINT `invoice_payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE",
    "ALTER TABLE `expenses` ADD CONSTRAINT `expenses_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE",
    "ALTER TABLE `agent_payments` ADD CONSTRAINT `agent_payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE",
    "INSERT INTO `bank_accounts` (`bank_name`, `account_number`, `ifsc_code`, `branch_name`, `account_type`, `current_balance`, `status`, `created_at`, `updated_at`)
     SELECT 'Cash in Hand', 'CASH-001', 'N/A', 'Cash Account', 'Cash', 0.00, 'active', NOW(), NOW()
     WHERE NOT EXISTS (SELECT 1 FROM `bank_accounts` WHERE `account_number` = 'CASH-001')"
];

// Execute each statement
foreach ($sql_statements as $index => $sql) {
    echo "Executing statement " . ($index + 1) . "...\n";
    echo substr($sql, 0, 80) . "...\n";
    
    if ($mysqli->query($sql)) {
        echo "✓ Success\n\n";
    } else {
        // Check if error is "Duplicate column" which is OK
        if (strpos($mysqli->error, 'Duplicate column') !== false || 
            strpos($mysqli->error, 'Duplicate key') !== false ||
            strpos($mysqli->error, 'already exists') !== false) {
            echo "⊘ Already exists (OK)\n\n";
        } else {
            echo "✗ Error: " . $mysqli->error . "\n\n";
        }
    }
}

echo "\n=== Final Schema Check ===\n";
$result = $mysqli->query("SHOW COLUMNS FROM bank_accounts");
echo "bank_accounts columns:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

$mysqli->close();
echo "\nDone!\n";
