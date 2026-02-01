<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 1. Modify Status Enum
$sql1 = "ALTER TABLE product_items MODIFY COLUMN status ENUM('received', 'available', 'sold', 'damaged', 'returned', 'rejected') DEFAULT 'received'";
if ($mysqli->query($sql1) === TRUE) {
    echo "Modified status enum successfully.\n";
} else {
    echo "Error modifying status: " . $mysqli->error . "\n";
}

// 2. Modify is_approved Enum
// First to include all possibilities to prevent data truncation
$sql2 = "ALTER TABLE product_items MODIFY COLUMN is_approved ENUM('Pending', 'Approved', 'Rejected', 'Yes', 'No') DEFAULT 'Pending'";
if ($mysqli->query($sql2) === TRUE) {
    echo "Expanded is_approved enum successfully.\n";
} else {
    echo "Error expanding is_approved: " . $mysqli->error . "\n";
}

// Migrate data
$mysqli->query("UPDATE product_items SET is_approved = 'Pending', status = 'received' WHERE is_approved = 'No'");
$mysqli->query("UPDATE product_items SET is_approved = 'Approved' WHERE is_approved = 'Yes'");

// Finalize Enum
$sql3 = "ALTER TABLE product_items MODIFY COLUMN is_approved ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'";
if ($mysqli->query($sql3) === TRUE) {
    echo "Finalized is_approved enum successfully.\n";
} else {
    echo "Error finalizing is_approved: " . $mysqli->error . "\n";
}

// 3. Add Rejection Columns
$sql4 = "ALTER TABLE product_items ADD rejection_reason TEXT DEFAULT NULL AFTER approved_at";
if ($mysqli->query($sql4) === TRUE) {
    echo "Added rejection_reason successfully.\n";
} else {
    echo "Error adding rejection_reason: " . $mysqli->error . "\n"; // might exist?
}

$sql5 = "ALTER TABLE product_items ADD rejection_image VARCHAR(255) DEFAULT NULL AFTER rejection_reason";
if ($mysqli->query($sql5) === TRUE) {
    echo "Added rejection_image successfully.\n";
} else {
    echo "Error adding rejection_image: " . $mysqli->error . "\n";
}

// 4. Record migration manually to prevent future run
$sql6 = "INSERT INTO migrations (version, class, `group`, namespace, time, batch) VALUES ('2024-01-22-100008', 'EnhanceApprovalProcess', 'default', 'App\\\\Database\\\\Migrations', " . time() . ", 2)";
$mysqli->query($sql6); 

$mysqli->close();
