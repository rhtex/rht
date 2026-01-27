<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check vendors id type
echo "--- VENDORS Columns ---\n";
$result = $mysqli->query("DESCRIBE vendors");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'id') {
        print_r($row);
    }
}

// Check users id type
echo "\n--- USERS Columns ---\n";
$result = $mysqli->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'id') {
        print_r($row);
    }
}

// Try to create bills table
echo "\n--- Creating Bills Table ---\n";
$sql = "CREATE TABLE `bills` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `vendor_id` INT(11) UNSIGNED NOT NULL,
    `bill_number` VARCHAR(50) NOT NULL UNIQUE,
    `zoho_bill_id` VARCHAR(100) NULL,
    `zoho_sync_status` ENUM('Pending', 'Synced', 'Failed') DEFAULT 'Pending',
    `bill_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `reference_number` VARCHAR(100) NULL,
    `status` ENUM('Draft', 'Open', 'Paid', 'Partially Paid', 'Overdue', 'Void') DEFAULT 'Draft',
    `subtotal` DECIMAL(12,2) DEFAULT 0.00,
    `cgst_amount` DECIMAL(12,2) DEFAULT 0.00,
    `sgst_amount` DECIMAL(12,2) DEFAULT 0.00,
    `igst_amount` DECIMAL(12,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) DEFAULT 0.00,
    `paid_amount` DECIMAL(12,2) DEFAULT 0.00,
    `balance` DECIMAL(12,2) DEFAULT 0.00,
    `notes` TEXT NULL,
    `terms` TEXT NULL,
    `created_by` INT(11) UNSIGNED NULL,
    `updated_by` INT(11) UNSIGNED NULL,
    `zoho_sync_at` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    CONSTRAINT `pk_bills` PRIMARY KEY(`id`),
    CONSTRAINT `bills_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `bills_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($mysqli->query($sql) === TRUE) {
    echo "Table bills created successfully.";
} else {
    echo "Error creating table: " . $mysqli->error;
}

$mysqli->close();
