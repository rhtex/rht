<?php
$conn = mysqli_connect('localhost', 'root', '', 'rasidev_hr');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$tables = ['sales_orders', 'quotations', 'sales_returns', 'bills'];
$column = 'is_inter_state';
$definition = 'TINYINT(1) DEFAULT 0';

foreach ($tables as $table) {
    $res = mysqli_query($conn, "DESCRIBE $table");
    $exists = false;
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['Field'] === $column) {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        echo "Adding column $column to $table..." . PHP_EOL;
        if (mysqli_query($conn, "ALTER TABLE $table ADD COLUMN $column $definition")) {
            echo "Successfully added $column to $table." . PHP_EOL;
        } else {
            echo "Error adding $column to $table: " . mysqli_error($conn) . PHP_EOL;
        }
    } else {
        echo "Column $column already exists in $table." . PHP_EOL;
    }
}

mysqli_close($conn);
echo "Schema update complete." . PHP_EOL;
