<?php
// Connect to database
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get all permission IDs
$result = $mysqli->query("SELECT id FROM permissions");
$permission_ids = [];
while ($row = $result->fetch_assoc()) {
    $permission_ids[] = $row['id'];
}

echo "Found " . count($permission_ids) . " total permissions.\n";

// Assign to Role ID 1 (Super Admin)
$role_id = 1;
$added = 0;

foreach ($permission_ids as $perm_id) {
    // Check if exists
    $check = $mysqli->query("SELECT 1 FROM role_permissions WHERE role_id = $role_id AND permission_id = $perm_id");
    if ($check->num_rows == 0) {
        $mysqli->query("INSERT INTO role_permissions (role_id, permission_id, created_at, updated_at) VALUES ($role_id, $perm_id, NOW(), NOW())");
        $added++;
    }
}

echo "Assigned $added new permissions to Role ID $role_id (Super Admin).\n";

$mysqli->close();
