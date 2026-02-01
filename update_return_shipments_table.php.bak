<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add waybill_image and delivery_status
$sql = "ALTER TABLE return_shipments ADD COLUMN waybill_image VARCHAR(255) NULL AFTER ewaybill_number, 
                            ADD COLUMN delivery_status ENUM('Pending', 'In Transit', 'Completed', 'Cancelled') DEFAULT 'Pending' AFTER waybill_image";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully to return_shipments";
} else {
    echo "Error adding columns: " . $conn->error;
}

$conn->close();
?>
