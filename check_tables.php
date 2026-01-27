<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// 1. Create Return Shipments
$sql = "CREATE TABLE IF NOT EXISTS `return_shipments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `reference_no` VARCHAR(50) NOT NULL,
    `return_date` DATE NOT NULL,
    `transport_name` VARCHAR(100) NULL,
    `waybill_number` VARCHAR(100) NULL,
    `waybill_date` DATE NULL,
    `packages_count` INT NULL,
    `ewaybill_number` VARCHAR(50) NULL,
    `status` VARCHAR(20) DEFAULT 'Pending',
    `item_count` INT DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) echo "Table return_shipments checked.\n";
else echo "Error return_shipments: " . mysqli_error($conn) . "\n";

// 2. Add column to product_items
$result = mysqli_query($conn, "SHOW COLUMNS FROM `product_items` LIKE 'return_shipment_id'");
if (mysqli_num_rows($result) == 0) {
    if(mysqli_query($conn, "ALTER TABLE `product_items` ADD COLUMN `return_shipment_id` INT NULL DEFAULT NULL AFTER `vendor_id`")) {
        echo "Column return_shipment_id added.\n";
    } else {
        echo "Error adding column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Column return_shipment_id already exists.\n";
}
