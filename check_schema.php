<?php
$c = new mysqli('localhost', 'root', '', 'rasidev_hr');
if ($c->connect_error) die("Connection failed: " . $c->connect_error);

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
