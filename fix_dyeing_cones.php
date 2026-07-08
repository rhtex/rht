<?php
/**
 * One-time fix: Backfill quantity_cones in production_yarn_stock_movements
 * for dyeing Receipt_Job_Work entries that are missing cones data.
 *
 * Run via: http://localhost/RHT/fix_dyeing_cones.php
 * or CLI:  php fix_dyeing_cones.php
 */

// Bootstrap CodeIgniter
require_once __DIR__ . '/vendor/autoload.php';

// Minimal CI bootstrap for DB
$db_config = [
    'hostname' => 'localhost',
    'database' => 'rasidev_hr',
    'username' => 'rasidev_hr',
    'password' => 'Mirage@WL2026',
    'DBDriver' => 'MySQLi',
    'port'     => 3306,
];

$db = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database'], $db_config['port']);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "<pre>\n";
echo "=== Backfill Dyeing Receipt Cones in Stock Movements ===\n\n";

// Find all Receipt_Job_Work movements where quantity_cones is 0 or NULL
$query = "SELECT sm.id, sm.reference_id, sm.yarn_count, sm.brand_mill, sm.quantity_cones
          FROM production_yarn_stock_movements sm
          WHERE sm.movement_type = 'Receipt_Job_Work'
          AND (sm.quantity_cones IS NULL OR sm.quantity_cones = 0)";

$result = $db->query($query);

if (!$result) {
    die("Query error: " . $db->error);
}

$updated = 0;
$skipped = 0;

while ($row = $result->fetch_assoc()) {
    $movementId = $row['id'];
    $receiptId = $row['reference_id'];

    // Find the receipt items for this receipt that have cones data
    $itemQuery = "SELECT ri.cones_received
                  FROM production_yarn_dyeing_receipt_items ri
                  JOIN production_yarn_dyeing_dc_items di ON di.id = ri.dc_item_id
                  WHERE ri.receipt_id = ?
                  AND di.yarn_count = ?
                  AND di.mill_name = ?
                  AND ri.cones_received > 0
                  LIMIT 1";

    $stmt = $db->prepare($itemQuery);
    $stmt->bind_param('iss', $receiptId, $row['yarn_count'], $row['brand_mill']);
    $stmt->execute();
    $itemResult = $stmt->get_result();

    if ($itemRow = $itemResult->fetch_assoc()) {
        $cones = (int)$itemRow['cones_received'];
        $updateStmt = $db->prepare("UPDATE production_yarn_stock_movements SET quantity_cones = ? WHERE id = ?");
        $updateStmt->bind_param('ii', $cones, $movementId);
        $updateStmt->execute();
        echo "Updated movement #$movementId (receipt #$receiptId): set quantity_cones = $cones\n";
        $updated++;
    } else {
        echo "Skipped movement #$movementId (receipt #$receiptId): no cones data found in receipt items\n";
        $skipped++;
    }
}

echo "\n=== Done ===\n";
echo "Updated: $updated\n";
echo "Skipped: $skipped\n";
echo "</pre>";

$db->close();
