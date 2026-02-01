<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) safe_die("Connection failed: " . mysqli_connect_error());

// Create Weavers Table
$sql = "CREATE TABLE IF NOT EXISTS `weavers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(50) UNIQUE NULL,
    `phone` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT NULL,
    `updated_by` INT NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'weavers' created successfully.\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// Add Permissions
$permissions = [
    'production.view',
    'weaver.view',
    'weaver.create',
    'weaver.edit',
    'weaver.delete'
];

foreach ($permissions as $perm) {
    // Check if permission exists
    $check = mysqli_query($conn, "SELECT id FROM permissions WHERE name = '$perm'");
    if (mysqli_num_rows($check) == 0) {
        $insert = "INSERT INTO permissions (name, description, module) VALUES ('$perm', 'Permission for $perm', 'production')";
        if (mysqli_query($conn, $insert)) {
            echo "Permission '$perm' added.\n";
        } else {
            echo "Error adding permission '$perm': " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Permission '$perm' already exists.\n";
    }
}

// Assign permissions to Admin role (assuming role_id 1 is Admin)
$role_id = 1;
foreach ($permissions as $perm) {
    $perm_id_query = mysqli_query($conn, "SELECT id FROM permissions WHERE name = '$perm'");
    $perm_row = mysqli_fetch_assoc($perm_id_query);
    $perm_id = $perm_row['id'];

    $check_role = mysqli_query($conn, "SELECT * FROM role_permissions WHERE role_id = $role_id AND permission_id = $perm_id");
    if (mysqli_num_rows($check_role) == 0) {
        mysqli_query($conn, "INSERT INTO role_permissions (role_id, permission_id) VALUES ($role_id, $perm_id)");
        echo "Permission '$perm' assigned to Admin.\n";
    }
}

mysqli_close($conn);
