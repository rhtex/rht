<?php require_once __DIR__ . '/debug_helpers.php'; ?>

$conn = new mysqli("localhost", "root", "", "rasidev_hr");
if ($conn->connect_error) safe_die("Connection failed: " . $conn->connect_error);

$output = "";
$tables = ['bank_transactions', 'invoice_payments', 'payments', 'expenses', 'invoices', 'bills'];
foreach($tables as $table) {
    $output .= "--- TABLE: $table ---\n";
    $result = $conn->query("DESCRIBE $table");
    while($row = $result->fetch_assoc()) {
        $output .= sprintf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
    }
    $output .= "\n";
}
file_put_contents('db_schema_logs.txt', $output);
$conn->close();
echo "Schema written to db_schema_logs.txt";
