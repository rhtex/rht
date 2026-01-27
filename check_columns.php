<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

function show_columns($conn, $table) {
    echo "\n--- Columns for $table ---\n";
    $res = mysqli_query($conn, "SHOW COLUMNS FROM $table");
    if (!$res) { echo "Error: " . mysqli_error($conn) . "\n"; return; }
    while ($row = mysqli_fetch_assoc($res)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
}

show_columns($conn, 'permissions');
show_columns($conn, 'role_permissions');
