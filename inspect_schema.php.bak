<?php
require 'vendor/autoload.php';
require 'app/Config/Constants.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Common.php';
$db = \Config\Database::connect();

foreach (['customers', 'vendors'] as $table) {
    echo "--- Table: $table ---\n";
    $fields = $db->getFieldNames($table);
    print_r($fields);
}
