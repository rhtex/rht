<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
if ($mysqli->connect_error) {
    safe_die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SHOW COLUMNS FROM product_items");
$cols = [];
while($row = $result->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo implode(", ", $cols);
$mysqli->close();
