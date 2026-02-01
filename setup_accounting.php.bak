<?php
$conn = new mysqli("localhost", "root", "", "rasidev_hr");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 1. Create accounts table
$sql1 = "CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('Asset', 'Liability', 'Equity', 'Income', 'Expense', 'COGS') NOT NULL,
    description TEXT,
    balance DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// 2. Create ledger_entries table
$sql2 = "CREATE TABLE IF NOT EXISTS ledger_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    entry_date DATE NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0.00,
    credit DECIMAL(15,2) DEFAULT 0.00,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
)";

if ($conn->query($sql1) && $conn->query($sql2)) {
    echo "Accounting tables created successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

// 3. Seed default accounts
$accounts = [
    ['Accounts Receivable', 'Asset'],
    ['Accounts Payable', 'Liability'],
    ['Cash', 'Asset'],
    ['Bank Account', 'Asset'],
    ['Sales Income', 'Income'],
    ['Cost of Goods Sold', 'COGS'],
    ['Cost of Purchase', 'COGS'],
    ['Customer Discount', 'Expense'],
    ['Vendor Payment Discount', 'Income'],
    ['Sales Return', 'Expense'],
    ['Purchase Return', 'Income'],
    ['GST Payable', 'Liability'],
    ['GST Receivable', 'Asset'],
    ['Mahimai Income', 'Income'],
    ['Postal Charges', 'Income'],
    ['Agent Commission Expense', 'Expense']
];

foreach ($accounts as $acc) {
    $name = $acc[0];
    $type = $acc[1];
    $check = $conn->query("SELECT id FROM accounts WHERE name = '$name'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO accounts (name, type) VALUES ('$name', '$type')");
    }
}

echo "Standard accounts seeded.\n";

$conn->close();
?>
