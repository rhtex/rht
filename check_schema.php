<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$c = new mysqli('localhost', 'root', '', 'rasidev_hr');
if ($c->connect_error) safe_die("Connection failed: " . $c->connect_error);

echo "Table: ledger_entries\n";
$res = $c->query('DESCRIBE ledger_entries');
while($row = $res->fetch_assoc()) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\nTable: accounts\n";
$res = $c->query('DESCRIBE accounts');
while($row = $res->fetch_assoc()) {
    echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
