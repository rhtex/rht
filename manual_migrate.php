<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 1. Rename item_image to received_image
$sql1 = "ALTER TABLE product_items CHANGE item_image received_image VARCHAR(255) DEFAULT NULL";
if ($mysqli->query($sql1) === TRUE) {
    echo "Renamed item_image to received_image successfully.\n";
} else {
    echo "Error renaming column: " . $mysqli->error . "\n";
}

// 2. Add verified_image
$sql2 = "ALTER TABLE product_items ADD verified_image VARCHAR(255) DEFAULT NULL AFTER received_image";
if ($mysqli->query($sql2) === TRUE) {
    echo "Added verified_image successfully.\n";
} else {
    echo "Error adding verified_image: " . $mysqli->error . "\n";
}

// 3. Add is_approved
$sql3 = "ALTER TABLE product_items ADD is_approved ENUM('Yes', 'No') DEFAULT 'No' AFTER status";
if ($mysqli->query($sql3) === TRUE) {
    echo "Added is_approved successfully.\n";
} else {
    echo "Error adding is_approved: " . $mysqli->error . "\n";
}

// 4. Add approved_by
$sql4 = "ALTER TABLE product_items ADD approved_by INT(11) UNSIGNED DEFAULT NULL AFTER is_approved";
if ($mysqli->query($sql4) === TRUE) {
    echo "Added approved_by successfully.\n";
} else {
    echo "Error adding approved_by: " . $mysqli->error . "\n";
}

// 5. Add approved_at
$sql5 = "ALTER TABLE product_items ADD approved_at DATETIME DEFAULT NULL AFTER approved_by";
if ($mysqli->query($sql5) === TRUE) {
    echo "Added approved_at successfully.\n";
} else {
    echo "Error adding approved_at: " . $mysqli->error . "\n";
}

// 6. Add manual migration entry to avoid future conflict
$sql6 = "INSERT INTO migrations (version, class, `group`, namespace, time, batch) VALUES ('2024-01-22-100007', 'AddVerificationToProductItems', 'default', 'App\\\\Database\\\\Migrations', " . time() . ", 1)";
if ($mysqli->query($sql6) === TRUE) {
    echo "Added migration entry successfully.\n";
} else {
    echo "Error adding migration entry: " . $mysqli->error . "\n";
}

$mysqli->close();
