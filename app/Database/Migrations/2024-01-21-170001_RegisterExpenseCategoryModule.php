<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterExpenseCategoryModule extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Register Module
        $exists = $db->table('modules')->where('module_slug', 'expense_category')->get()->getRow();
        if (!$exists) {
            $db->table('modules')->insert([
                'module_name' => 'Expense Categories',
                'module_slug' => 'expense_category',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Add permissions if they don't exist (I'm using expense.view/create/edit for now in routes, but let's add specific ones too)
        // Actually, my routes use expense.* permissions. That's fine for now to keep it simple for the user.
    }

    public function down()
    {
    }
}
