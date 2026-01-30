<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}
$conn->close();
