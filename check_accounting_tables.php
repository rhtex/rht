<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = new mysqli("localhost", "root", "", "rasidev_hr");
if ($conn->connect_error) safe_die("Connection failed: " . $conn->connect_error);

$tables = ['bank_transactions', 'invoice_payments', 'payments', 'expenses'];
foreach($tables as $table) {
    echo "--- TABLE: $table ---\n";
    $result = $conn->query("DESCRIBE $table");
    while($row = $result->fetch_assoc()) {
        printf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
    }
    echo "\n";
}
$conn->close();
