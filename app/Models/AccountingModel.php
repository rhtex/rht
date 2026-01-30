<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountingModel extends Model
{
    protected $table            = 'ledger_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'account_id', 'entry_date', 'debit', 'credit', 
        'description', 'reference_type', 'reference_id',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = false;

    /**
     * Get account ID by name
     */
    public function getAccountId($name)
    {
        $db = \Config\Database::connect();
        $row = $db->table('accounts')->where('name', $name)->get()->getRowArray();
        return $row ? $row['id'] : null;
    }

    /**
     * Post a single side of an entry and update account balance
     */
    public function postEntry($accountName, $date, $debit, $credit, $description, $refType, $refId)
    {
        $accountId = $this->getAccountId($accountName);
        if (!$accountId) {
            log_message('error', "Account '$accountName' not found for posting entry.");
            return false;
        }

        $data = [
            'account_id'     => $accountId,
            'entry_date'     => $date,
            'debit'          => $debit,
            'credit'         => $credit,
            'description'    => $description,
            'reference_type' => $refType,
            'reference_id'   => $refId
        ];

        $this->insert($data);

        // Update account balance
        $this->updateAccountBalance($accountId);
        return true;
    }

    /**
     * Update the cached balance in accounts table
     */
    public function updateAccountBalance($accountId)
    {
        $db = \Config\Database::connect();
        $summary = $db->table($this->table)
                      ->select('SUM(debit) as total_debit, SUM(credit) as total_credit')
                      ->where('account_id', $accountId)
                      ->get()
                      ->getRowArray();
        
        $totalDebit = (float)($summary['total_debit'] ?? 0);
        $totalCredit = (float)($summary['total_credit'] ?? 0);

        // Logic for balance depends on account type:
        // Assets/Expenses: Balance = Debit - Credit
        // Liabilities/Income/Equity: Balance = Credit - Debit
        $account = $db->table('accounts')->where('id', $accountId)->get()->getRowArray();
        if (!$account) return;

        $balance = 0;
        if (in_array($account['type'], ['Asset', 'Expense', 'COGS'])) {
            $balance = $totalDebit - $totalCredit;
        } else {
            $balance = $totalCredit - $totalDebit;
        }

        $db->table('accounts')->where('id', $accountId)->update(['balance' => $balance]);
    }
}
