<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE invoices ADD COLUMN waybill_image VARCHAR(255) NULL AFTER ewaybill_number";

if ($conn->query($sql) === TRUE) {
    echo "Column waybill_image added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
