<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerOrderDesignsTable extends Migration
{
    public function up()
    {
        // 1. Add order_number to customer_orders if not present
        if ($this->db->tableExists('customer_orders') && !$this->db->fieldExists('order_number', 'customer_orders')) {
            $this->forge->addColumn('customer_orders', [
                'order_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }

        // 2. Create child table customer_order_designs (no status column — status is order-level only)
        if (!$this->db->tableExists('customer_order_designs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'customer_order_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'design_no' => [
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
                'rate' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => '0.00',
                ],
                'image' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
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
            $this->forge->addKey('customer_order_id');
            $this->forge->createTable('customer_order_designs');
        }

        // 3. Migrate existing single-design rows in customer_orders without data loss
        if ($this->db->tableExists('customer_orders') && $this->db->tableExists('customer_order_designs')) {
            $existingOrders = $this->db->table('customer_orders')->get()->getResultArray();
            $now = date('Y-m-d H:i:s');
            foreach ($existingOrders as $ord) {
                $mappedStatus = in_array(($ord['status'] ?? ''), ['Closed', 'Completed', 'Cancelled'], true) ? 'Closed' : 'Active';
                $upd = ['status' => $mappedStatus];
                if (empty($ord['order_number'])) {
                    $upd['order_number'] = 'CO-' . str_pad((string) $ord['id'], 4, '0', STR_PAD_LEFT);
                }
                $this->db->table('customer_orders')->where('id', $ord['id'])->update($upd);

                $hasDesigns = $this->db->table('customer_order_designs')
                    ->where('customer_order_id', $ord['id'])
                    ->countAllResults();
                if ($hasDesigns === 0 && (!empty($ord['product_name']) || !empty($ord['design_no']))) {
                    $this->db->table('customer_order_designs')->insert([
                        'customer_order_id' => $ord['id'],
                        'design_no'         => 1,
                        'product_id'        => $ord['product_id'] ?? null,
                        'product_name'      => $ord['product_name'] ?: ('Design ' . ($ord['design_no'] ?? '1')),
                        'rate'              => $ord['rate'] ?? 0,
                        'image'             => $ord['image'] ?? null,
                        'created_at'        => $ord['created_at'] ?? $now,
                        'updated_at'        => $ord['updated_at'] ?? $now,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('customer_order_designs', true);
        if ($this->db->tableExists('customer_orders') && $this->db->fieldExists('order_number', 'customer_orders')) {
            $this->forge->dropColumn('customer_orders', 'order_number');
        }
    }
}
