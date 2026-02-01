<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = new mysqli("localhost", "root", "", "rasidev_hr");
if ($conn->connect_error) safe_die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM expense_categories");
while($row = $result->fetch_assoc()) {
    safe_print_r($row);
}
$conn->close();
