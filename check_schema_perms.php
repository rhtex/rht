<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) safe_die("Connection failed: " . mysqli_connect_error());

echo "PERMISSIONS TABLE:\n";
$res = mysqli_query($conn, "DESCRIBE permissions");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field']."\n";
}

echo "\nROLE_PERMISSIONS TABLE:\n";
$res = mysqli_query($conn, "DESCRIBE role_permissions");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field']."\n";
}

echo "\nROLES TABLE:\n";
$res = mysqli_query($conn, "DESCRIBE roles");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field']."\n";
}

echo "\nCHECK EXISTING WEAVER PERMISSIONS:\n";
$res = mysqli_query($conn, "SELECT * FROM permissions WHERE name LIKE '%weaver%'");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . " Name: " . $row['name'] . "\n";
}
