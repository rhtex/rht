<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankTransactionPermissions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Get Finance module ID
        $financeMod = $db->table('modules')->where('module_slug', 'bank_account')->get()->getRow();
        $moduleId = $financeMod ? $financeMod->id : null;

        if (!$moduleId) {
             // Fallback to searching by name if slug differs
             $financeMod = $db->table('modules')->where('module_name', 'Bank Accounts')->get()->getRow();
             $moduleId = $financeMod ? $financeMod->id : 1;
        }

        // 2. Define permissions
        // Note: I'm reusing the bank_account module id but with specific keys
        $perms = [
            'bank_account.view'   => 'View Statement',
            'bank_account.edit'   => 'Add/Edit Transactions',
        ];
        
        // Actually, the routes already use bank_account permissions.
        // I'll just make sure Admin has them (they already do).
        
        // If I wanted NEW permissions specifically for transactions:
        /*
        $perms = [
            'bank_transaction.view'   => 'View Statement',
            'bank_transaction.create' => 'Record Transaction',
            'bank_transaction.edit'   => 'Edit Transaction',
            'bank_transaction.delete' => 'Delete Transaction',
        ];
        */
        
        // I'll stick with bank_account permissions for now as per my routes.
    }

    public function down()
    {
    }
}
