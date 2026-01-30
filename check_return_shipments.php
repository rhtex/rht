<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");
$result = $conn->query("DESCRIBE return_shipments");
echo "Table Structure for return_shipments:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
$conn->close();
