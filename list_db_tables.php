<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) safe_die("Connection failed: " . mysqli_connect_error());

$res = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($res)) {
    echo $row[0] . "\n";
}
