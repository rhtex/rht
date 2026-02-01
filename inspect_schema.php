<?php require_once __DIR__ . '/debug_helpers.php'; ?>

require 'vendor/autoload.php';
require 'app/Config/Constants.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Common.php';
$db = \Config\Database::connect();

foreach (['customers', 'vendors'] as $table) {
    echo "--- Table: $table ---\n";
    $fields = $db->getFieldNames($table);
    safe_print_r($fields);
}
