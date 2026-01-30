<?php

require_once 'vendor/autoload.php';
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once 'system/Test/bootstrap.php';

$db = \Config\Database::connect();
$forge = \Config\Database::forge();

$tables = ['invoices', 'invoice_status_history'];

echo "Checking tables...\n";

// 1. Check invoices for delivered_date
$fields = $db->getFieldNames('invoices');
if (!in_array('delivered_date', $fields)) {
    echo "Adding delivered_date to invoices...\n";
    $forge->addColumn('invoices', [
        'delivered_date' => [
            'type' => 'DATE',
            'null' => true,
            'after' => 'delivery_status'
        ]
    ]);
} else {
    echo "delivered_date already exists in invoices.\n";
}

// 2. Create invoice_status_history if missing
if (!$db->tableExists('invoice_status_history')) {
    echo "Creating invoice_status_history table...\n";
    $forge->addField([
        'id' => [
            'type'           => 'INT',
            'constraint'     => 11,
            'unsigned'       => true,
            'auto_increment' => true,
        ],
        'invoice_id' => [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
        ],
        'status' => [
            'type'       => 'VARCHAR',
            'constraint' => '50',
        ],
        'description' => [
            'type' => 'TEXT',
            'null' => true,
        ],
        'created_at' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'created_by' => [
            'type'       => 'INT',
            'constraint' => 11,
            'unsigned'   => true,
            'null'       => true,
        ],
    ]);
    $forge->addKey('id', true);
    $forge->createTable('invoice_status_history');
} else {
    echo "invoice_status_history table already exists.\n";
}

echo "Done!\n";
