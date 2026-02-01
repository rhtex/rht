<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) safe_die("Connection failed: " . mysqli_connect_error());

// Check table
$res = mysqli_query($conn, "SHOW TABLES LIKE 'weavers'");
if (mysqli_num_rows($res) > 0) {
    echo "Table 'weavers' setup is OK.\n";
} else {
    echo "Table 'weavers' is MISSING.\n";
}

// Check Permissions
$perms = [
    'production.view',
    'weaver.view',
    'weaver.create',
    'weaver.edit',
    'weaver.delete'
];

foreach ($perms as $p) {
    $res = mysqli_query($conn, "SELECT id FROM permissions WHERE name = '$p'");
    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        // Check role assignment to Admin (1)
        $perm_id = $row['id'];
        $role_check = mysqli_query($conn, "SELECT * FROM role_permissions WHERE role_id = 1 AND permission_id = $perm_id");
        if (mysqli_num_rows($role_check) > 0) {
            echo "Permission '$p' is OK (and assigned to Admin).\n";
        } else {
            // Assign it
            mysqli_query($conn, "INSERT INTO role_permissions (role_id, permission_id) VALUES (1, $perm_id)");
            echo "Permission '$p' was present but not assigned. Assigned now.\n";
        }
    } else {
        echo "Permission '$p' is MISSING. Fixing...\n";
        mysqli_query($conn, "INSERT INTO permissions (name, description, module) VALUES ('$p', 'Permission for $p', 'production')");
        $id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO role_permissions (role_id, permission_id) VALUES (1, $id)");
    }
}
echo "Done.";
