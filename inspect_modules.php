<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

echo "--- MODULES SCHEMA ---\n";
$res = mysqli_query($conn, "DESCRIBE modules");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field']."\n";
}

echo "\n--- MODULES DATA ---\n";
$res = mysqli_query($conn, "SELECT * FROM modules");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['id'] . " | " . $row['module_key'] . " | " . $row['module_name'] . "\n";
}
