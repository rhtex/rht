<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
$result = $mysqli->query("SHOW COLUMNS FROM product_items WHERE Field IN ('approved_by', 'verified_image', 'received_image', 'item_image')");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " exists.\n";
}
$mysqli->close();
