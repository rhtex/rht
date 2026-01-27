<?php
$conn = new mysqli('localhost', 'root', '', 'rasidev_hr');
$tables = ['roles', 'modules', 'permissions', 'role_permissions'];
foreach ($tables as $t) {
    echo "Table: $t\n";
    $res = $conn->query("DESCRIBE $t");
    while($row = $res->fetch_assoc()) {
        echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    echo "\n";
}
$conn->close();
