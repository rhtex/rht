<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

echo "--- PERMISSIONS SAMPLE ---\n";
$res = mysqli_query($conn, "SELECT * FROM permissions LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

echo "\n--- MODULES SAMPLE ---\n";
$res = mysqli_query($conn, "SELECT * FROM modules LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

echo "\n--- ROLE_PERMISSIONS SCHEMA ---\n";
$res = mysqli_query($conn, "DESCRIBE role_permissions");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field']."\n";
}
