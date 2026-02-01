<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Update Invoices Table
$queries = [
    "ALTER TABLE `invoices` ADD COLUMN `packages_count` INT NULL DEFAULT NULL AFTER `waybill_number`",
    "ALTER TABLE `invoices` ADD COLUMN `waybill_date` DATE NULL DEFAULT NULL AFTER `packages_count`",
    "ALTER TABLE `invoices` ADD COLUMN `ewaybill_number` VARCHAR(50) NULL DEFAULT NULL AFTER `waybill_date`"
];

foreach ($queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Successfully executed: $sql\n";
    } else {
        echo "Error or already exists: " . mysqli_error($conn) . "\n";
    }
}

// 2. Create Return Shipments Table
$sql_shipments = "CREATE TABLE IF NOT EXISTS `return_shipments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `reference_no` VARCHAR(50) NOT NULL,
    `return_date` DATE NOT NULL,
    `transport_name` VARCHAR(100) NULL,
    `waybill_number` VARCHAR(100) NULL,
    `waybill_date` DATE NULL,
    `packages_count` INT NULL,
    `ewaybill_number` VARCHAR(50) NULL,
    `status` VARCHAR(20) DEFAULT 'Pending', -- Pending, Shipped, Delivered
    `item_count` INT DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`)
)";

if (mysqli_query($conn, $sql_shipments)) {
    echo "Table 'return_shipments' created successfully.\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// 3. Add column to product_items to link to return_shipment
$sql_link = "ALTER TABLE `product_items` ADD COLUMN `return_shipment_id` INT NULL DEFAULT NULL AFTER `vendor_id`";
if (mysqli_query($conn, $sql_link)) {
    echo "Added return_shipment_id to product_items.\n";
} else {
    echo "Error adding column: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
