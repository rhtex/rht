<?php
$c = new mysqli('localhost', 'root', '', 'rasidev_hr');
$res = $c->query('DESCRIBE ledger_entries');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "----\n";
$res = $c->query('DESCRIBE accounts');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
