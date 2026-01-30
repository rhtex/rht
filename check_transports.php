<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");
$result = $conn->query("SELECT DISTINCT transport_name FROM invoices");
echo "Unique Transport Names:\n";
while($row = $result->fetch_assoc()) {
    echo "[" . $row['transport_name'] . "]\n";
}
$conn->close();
