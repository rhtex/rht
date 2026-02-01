<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");

if ($mysqli->connect_error) {
    safe_die("Connection failed: " . $mysqli->connect_error);
}

// 1. Create bill_items
echo "--- Creating bill_items Table ---\n";
$sql_items = "CREATE TABLE IF NOT EXISTS `bill_items` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `bill_id` INT(11) UNSIGNED NOT NULL,
    `product_id` INT(11) UNSIGNED NULL,
    `description` VARCHAR(500) NOT NULL,
    `hsn_code` VARCHAR(20) NULL,
    `quantity` DECIMAL(10,2) DEFAULT 1.00,
    `rate` DECIMAL(12,2) DEFAULT 0.00,
    `tax_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `cgst_rate` DECIMAL(5,2) DEFAULT 0.00,
    `sgst_rate` DECIMAL(5,2) DEFAULT 0.00,
    `igst_rate` DECIMAL(5,2) DEFAULT 0.00,
    `amount` DECIMAL(12,2) DEFAULT 0.00,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    CONSTRAINT `pk_bill_items` PRIMARY KEY(`id`),
    CONSTRAINT `bill_items_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `bill_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($mysqli->query($sql_items) === TRUE) {
    echo "Table bill_items created successfully.\n";
} else {
    echo "Error creating bill_items: " . $mysqli->error . "\n";
}

// 2. Create payments
echo "\n--- Creating payments Table ---\n";
$sql_payments = "CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `bill_id` INT(11) UNSIGNED NOT NULL,
    `vendor_id` INT(11) UNSIGNED NOT NULL,
    `zoho_payment_id` VARCHAR(100) NULL,
    `bank_transaction_id` INT(11) UNSIGNED NULL,
    `payment_number` VARCHAR(50) NOT NULL UNIQUE,
    `payment_date` DATE NOT NULL,
    `payment_mode` ENUM('Cash', 'Bank Transfer', 'Cheque', 'Credit Card', 'Debit Card', 'UPI', 'Other') DEFAULT 'Bank Transfer',
    `amount` DECIMAL(12,2) DEFAULT 0.00,
    `reference_number` VARCHAR(100) NULL,
    `bank_account_id` INT(11) UNSIGNED NULL,
    `zoho_sync_status` ENUM('Pending', 'Synced', 'Failed') DEFAULT 'Pending',
    `notes` TEXT NULL,
    `created_by` INT(11) UNSIGNED NULL,
    `updated_by` INT(11) UNSIGNED NULL,
    `zoho_sync_at` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    CONSTRAINT `pk_payments` PRIMARY KEY(`id`),
    CONSTRAINT `payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `payments_bank_transaction_id_foreign` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($mysqli->query($sql_payments) === TRUE) {
    echo "Table payments created successfully.\n";
} else {
    echo "Error creating payments: " . $mysqli->error . "\n";
}

// 3. Create vendor_credits
echo "\n--- Creating vendor_credits Table ---\n";
$sql_credits = "CREATE TABLE IF NOT EXISTS `vendor_credits` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `vendor_id` INT(11) UNSIGNED NOT NULL,
    `amount` DECIMAL(10,2) DEFAULT 0.00,
    `reference_no` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `status` ENUM('Unused', 'Partial', 'Used') DEFAULT 'Unused',
    `used_amount` DECIMAL(10,2) DEFAULT 0.00,
    `created_by` INT(11) UNSIGNED NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    CONSTRAINT `pk_vendor_credits` PRIMARY KEY(`id`),
    CONSTRAINT `vendor_credits_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `vendor_credits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($mysqli->query($sql_credits) === TRUE) {
    echo "Table vendor_credits created successfully.\n";
} else {
    echo "Error creating vendor_credits: " . $mysqli->error . "\n";
}

$mysqli->close();
