<?php
$db = \Config\Database::connect();
$perms = $db->table('permissions')->select('permission_key')->get()->getResultArray();
foreach($perms as $p) {
    echo $p['permission_key'] . PHP_EOL;
}
?>