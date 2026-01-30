<?php
$db = \Config\Database::connect();
$fields = $db->getFieldNames('invoices');
foreach ($fields as $field) {
    echo $field . PHP_EOL;
}
