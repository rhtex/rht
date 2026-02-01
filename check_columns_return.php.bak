<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SHOW COLUMNS FROM product_items");
echo "Columns in product_items table:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
$mysqli->close();
