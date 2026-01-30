<?php
// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/app/Config/Paths.php';
$app = new \CodeIgniter\CodeIgniter(new \Config\Paths());
$app->initialize();

$db = \Config\Database::connect();
$fields = $db->getFieldNames('invoices');
echo "Columns in 'invoices':\n";
print_r($fields);

if (!in_array('waybill_image', $fields)) {
    echo "\n'waybill_image' is MISSING. Attempting to add it via SQL...\n";
    try {
        $db->query("ALTER TABLE invoices ADD COLUMN waybill_image VARCHAR(255) NULL AFTER ewaybill_number");
        echo "Successfully added 'waybill_image' column.\n";
    } catch (\Exception $e) {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n'waybill_image' ALREADY EXISTS.\n";
}
