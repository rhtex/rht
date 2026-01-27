<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Add return_action column
$sql = "ALTER TABLE product_items ADD return_action ENUM('Pending', 'Returned', 'Exchanged') DEFAULT 'Pending' AFTER rejection_image";

if ($mysqli->query($sql) === TRUE) {
    echo "Added return_action successfully.\n";
} else {
    echo "Error adding return_action: " . $mysqli->error . "\n";
}

// Record migration manually to prevent future run
$sql2 = "INSERT INTO migrations (version, class, `group`, namespace, time, batch) VALUES ('2024-01-22-120000', 'AddReturnActionToProductItems', 'default', 'App\\\\Database\\\\Migrations', " . time() . ", 3)";
$mysqli->query($sql2);

$mysqli->close();
