<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

echo "--- Permissions Check ---\n";
// Check if 'weaver.view' exists
$res = mysqli_query($conn, "SELECT * FROM permissions WHERE name LIKE '%weaver%'");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . "\n";
}

echo "\n--- Roles Check ---\n";
$res = mysqli_query($conn, "SELECT * FROM roles");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . "\n";
}

echo "\n--- Role Permissions (for Role ID 1) ---\n";
// Check permissions for Role 1
$res = mysqli_query($conn, "
    SELECT p.name 
    FROM role_permissions rp 
    JOIN permissions p ON p.id = rp.permission_id 
    WHERE rp.role_id = 1 AND p.name LIKE '%weaver%'
");
if (mysqli_num_rows($res) > 0) {
    echo "Role 1 has the following weaver permissions:\n";
    while ($row = mysqli_fetch_assoc($res)) {
        echo "- " . $row['name'] . "\n";
    }
} else {
    echo "Role 1 has NO weaver permissions assigned.\n";
}

// Check Users
echo "\n--- Users Check ---\n";
$res = mysqli_query($conn, "SELECT id, email, role_id FROM users LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) {
    echo "User ID: " . $row['id'] . " | Email: " . $row['email'] . " | Role ID: " . $row['role_id'] . "\n";
}

mysqli_close($conn);
