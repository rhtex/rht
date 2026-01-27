<?php
$m = new mysqli('localhost', 'root', '', 'rasidev_hr');
$r = $m->query('SELECT * FROM migrations');
while($w = $r->fetch_assoc()) {
    echo $w['version'] . " - " . $w['class'] . " - Batch: " . $w['batch'] . "\n";
}
$m->close();
