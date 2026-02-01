<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM expense_categories");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
$conn->close();
