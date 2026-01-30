<?php
// Quick check to see current bank_accounts schema
$db = \Config\Database::connect();
$query = $db->query("SHOW COLUMNS FROM bank_accounts");
echo "=== Current bank_accounts columns ===\n";
foreach ($query->getResultArray() as $column) {
    echo $column['Field'] . " - " . $column['Type'] . "\n";
}

echo "\n=== Migration status ===\n";
$query = $db->query("SELECT * FROM migrations ORDER BY version DESC LIMIT 10");
foreach ($query->getResultArray() as $migration) {
    echo $migration['version'] . " - " . $migration['class'] . " - " . $migration['group'] . "\n";
}
