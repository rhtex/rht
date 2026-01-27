<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// 1. Ensure Module Exists
$module_name = 'Production';
$module_slug = 'production';
$module_id = 0;

$res = mysqli_query($conn, "SELECT id FROM modules WHERE module_slug = '$module_slug'");
if (mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    $module_id = $row['id'];
    echo "Module 'Production' exists (ID: $module_id).\n";
} else {
    $sql = "INSERT INTO modules (module_name, module_slug, status) VALUES ('$module_name', '$module_slug', 'active')";
    if (mysqli_query($conn, $sql)) {
        $module_id = mysqli_insert_id($conn);
        echo "Module 'Production' created (ID: $module_id).\n";
    } else {
        die("Error creating module: " . mysqli_error($conn));
    }
}

// 2. Permissions
$perms = [
    'production.view' => 'Access Production Module',
    'weaver.view'     => 'View Weavers',
    'weaver.create'   => 'Create Weaver',
    'weaver.edit'     => 'Edit Weaver',
    'weaver.delete'   => 'Delete Weaver'
];

foreach ($perms as $key => $name) {
    $perm_id = 0;
    // Check if permission exists
    $check = mysqli_query($conn, "SELECT id FROM permissions WHERE permission_key = '$key'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $perm_id = $row['id'];
        echo "Permission '$key' exists (ID: $perm_id).\n";
        
        // Update module_id if needed (optional but good for consistency)
        mysqli_query($conn, "UPDATE permissions SET module_id = $module_id WHERE id = $perm_id AND module_id IS NULL");
    } else {
        $insert = "INSERT INTO permissions (permission_key, permission_name, module_id) VALUES ('$key', '$name', $module_id)";
        if (mysqli_query($conn, $insert)) {
            $perm_id = mysqli_insert_id($conn);
            echo "Permission '$key' created (ID: $perm_id).\n";
        } else {
            echo "Error creating permission '$key': " . mysqli_error($conn) . "\n";
            continue;
        }
    }

    // 3. Assign to Admin Role (ID 1)
    $role_check = mysqli_query($conn, "SELECT * FROM role_permissions WHERE role_id = 1 AND permission_id = $perm_id");
    if (mysqli_num_rows($role_check) == 0) {
        if (mysqli_query($conn, "INSERT INTO role_permissions (role_id, permission_id) VALUES (1, $perm_id)")) {
            echo "Assigned '$key' to Role 1.\n";
        } else {
            echo "Error assigning '$key': " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "'$key' already assigned to Role 1.\n";
    }
}

echo "Done fixing permissions.\n";
mysqli_close($conn);
