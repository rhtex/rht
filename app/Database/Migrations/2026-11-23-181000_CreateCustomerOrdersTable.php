<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerOrdersTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('customer_orders')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'customer_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'product_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'design_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'rate' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => '0.00',
                ],
                'order_date' => [
                    'type' => 'DATE',
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'Pending',
                ],
                'image' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('customer_id');
            $this->forge->addKey('design_no');
            $this->forge->createTable('customer_orders');
        }

        if ($this->db->tableExists('invoice_items')) {
            if (!$this->db->fieldExists('design_no', 'invoice_items')) {
                $this->forge->addColumn('invoice_items', [
                    'design_no' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'null'       => true,
                        'after'      => 'description',
                    ],
                ]);
            }
            if (!$this->db->fieldExists('customer_order_id', 'invoice_items')) {
                $this->forge->addColumn('invoice_items', [
                    'customer_order_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'design_no',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('customer_orders', true);
        if ($this->db->tableExists('invoice_items')) {
            if ($this->db->fieldExists('design_no', 'invoice_items')) {
                $this->forge->dropColumn('invoice_items', 'design_no');
            }
            if ($this->db->fieldExists('customer_order_id', 'invoice_items')) {
                $this->forge->dropColumn('invoice_items', 'customer_order_id');
            }
        }
    }
}
