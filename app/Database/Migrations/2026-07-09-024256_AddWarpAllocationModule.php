<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarpAllocationModule extends Migration
{
    public function up()
    {
        // 1. Modify weavers table (if not already done by previous migrations)
        if (!$this->db->fieldExists('customer_id', 'weavers')) {
            $this->forge->addColumn('weavers', [
                'customer_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'status'
                ],
                'vendor_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'customer_id'
                ]
            ]);
        }

        // 2. Create weaver_looms table (if not exists)
        if (!$this->db->tableExists('weaver_looms')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'weaver_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'loom_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'contract_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Job Work', 'Sale & Buy Back'],
                    'default'    => 'Job Work',
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Active', 'Inactive'],
                    'default'    => 'Active',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('weaver_id');
            $this->forge->createTable('weaver_looms');
        }

        // 3. Create production_allocations table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'allocation_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'allocation_date' => [
                'type' => 'DATE',
            ],
            'weaver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'loom_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // In case they only issue weft and don't care about loom? Let's make it nullable just in case.
            ],
            'warp_beam_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Optional
            ],
            'contract_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Job Work', 'Sale & Buy Back'],
            ],
            'expected_return_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Allocated', 'In Weaving', 'Partially Returned', 'Completed', 'Cancelled'],
                'default'    => 'Allocated',
            ],
            'reference_document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['DC', 'Sales Invoice'],
            ],
            'reference_document_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('weaver_id');
        $this->forge->addKey('warp_beam_id');
        $this->forge->createTable('production_allocations');

        // 4. Create production_allocation_wefts table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'allocation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'yarn_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'color_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'batch_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'issued_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0,
            ],
            'rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('allocation_id');
        $this->forge->createTable('production_allocation_wefts');

        // 5. Create production_receipts table
        if (!$this->db->tableExists('production_receipts')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'receipt_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'receipt_date' => [
                    'type' => 'DATE',
                ],
                'weaver_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'allocation_id' => [ // Changed from beam_allocation_id
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Pending QC', 'Approved', 'Cancelled'],
                    'default'    => 'Pending QC',
                ],
                'reference_document_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Job Work Receipt', 'Purchase Entry'],
                ],
                'reference_document_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'remarks' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('weaver_id');
            $this->forge->addKey('allocation_id');
            $this->forge->createTable('production_receipts');
        }

        // 6. Create production_receipt_items table
        if (!$this->db->tableExists('production_receipt_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'receipt_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'item_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Finished Goods', 'Waste', 'Returned Weft Yarn'],
                ],
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'quality_status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Good', 'Damaged'],
                    'default'    => 'Good',
                ],
                'quantity' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ],
                'weight' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,3',
                    'default'    => 0,
                ],
                'rate_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Per Saree', 'Per Meter', 'Per Pick'],
                    'null'       => true,
                ],
                'rate' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ],
                'wage_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('receipt_id');
            $this->forge->createTable('production_receipt_items');
        }

        // 7. Create production_weaver_settlements table
        if (!$this->db->tableExists('production_weaver_settlements')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'settlement_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'settlement_date' => [
                    'type' => 'DATE',
                ],
                'weaver_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'total_wages' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ],
                'deductions' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ],
                'net_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Pending', 'Paid'],
                    'default'    => 'Pending',
                ],
                'remarks' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ]
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('weaver_id');
            $this->forge->createTable('production_weaver_settlements');
        }
    }

    public function down()
    {
        $this->forge->dropTable('production_weaver_settlements', true);
        $this->forge->dropTable('production_receipt_items', true);
        $this->forge->dropTable('production_receipts', true);
        $this->forge->dropTable('production_allocation_wefts', true);
        $this->forge->dropTable('production_allocations', true);
        $this->forge->dropTable('weaver_looms', true);
        
        if ($this->db->fieldExists('vendor_id', 'weavers')) {
            $this->forge->dropColumn('weavers', 'vendor_id');
        }
        if ($this->db->fieldExists('customer_id', 'weavers')) {
            $this->forge->dropColumn('weavers', 'customer_id');
        }
    }
}
