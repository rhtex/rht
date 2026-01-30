<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDefaultCashAccount extends Migration
{
    public function up()
    {
        // Insert default "Cash in Hand" account
        $data = [
            'bank_name'       => 'Cash in Hand',
            'account_number'  => 'CASH-001',
            'ifsc_code'       => 'N/A',
            'branch_name'     => 'Cash Account',
            'account_type'    => 'Cash',
            'current_balance' => 0.00,
            'status'          => 'active',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        
        // Check if cash account already exists
        $db = \Config\Database::connect();
        $exists = $db->table('bank_accounts')
                    ->where('account_type', 'Cash')
                    ->orWhere('account_number', 'CASH-001')
                    ->get()
                    ->getRow();
        
        if (!$exists) {
            $db->table('bank_accounts')->insert($data);
        }
    }

    public function down()
    {
        // Remove the default cash account
        $db = \Config\Database::connect();
        $db->table('bank_accounts')
           ->where('account_number', 'CASH-001')
           ->delete();
    }
}
