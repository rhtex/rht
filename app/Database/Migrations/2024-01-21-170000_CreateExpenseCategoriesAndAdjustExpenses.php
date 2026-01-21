<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpenseCategoriesAndAdjustExpenses extends Migration
{
    public function up()
    {
        // 1. Create expense_categories table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->createTable('expense_categories');

        // 2. Add category_id to expenses table
        $this->forge->addColumn('expenses', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'expense_date',
            ],
        ]);

        // 3. Optional: Migrate existing categories if any
        $db = \Config\Database::connect();
        $expenses = $db->table('expenses')->get()->getResult();
        foreach ($expenses as $expense) {
            if (!empty($expense->category)) {
                $existing = $db->table('expense_categories')->where('category_name', $expense->category)->get()->getRow();
                if (!$existing) {
                    $db->table('expense_categories')->insert([
                        'category_name' => $expense->category,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s')
                    ]);
                    $catId = $db->insertID();
                } else {
                    $catId = $existing->id;
                }
                $db->table('expenses')->where('id', $expense->id)->update(['category_id' => $catId]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('expenses', 'category_id');
        $this->forge->dropTable('expense_categories');
    }
}
