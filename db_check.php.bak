<?php

// Manually load CI4
require 'vendor/autoload.php';
require 'app/Config/Constants.php';

// Setup paths
$paths = new \Config\Paths();

// Services setup
require $paths->systemDirectory . '/Common.php';

// Database connection
$db = \Config\Database::connect();

$tables = ['modules', 'permissions', 'role_permissions'];

foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $builder = $db->table($table);
    
    if ($table == 'modules') {
        $builder->where('slug', 'zoho_settings');
    } elseif ($table == 'permissions') {
        $builder->like('name', 'zoho');
    } elseif ($table == 'role_permissions') {
        // Just show all for now, or filter by zoho permissions if we had them
    }
    
    $results = $builder->get()->getResultArray();
    foreach ($results as $row) {
        print_r($row);
    }
}
echo "Done.\n";
