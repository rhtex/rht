<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceStatusHistoryModel extends Model
{
    protected $table            = 'invoice_status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['invoice_id', 'status', 'description', 'created_at', 'created_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getHistoryByInvoice($invoiceId)
    {
        return $this->select('invoice_status_history.*, users.name as user_name')
                    ->join('users', 'users.id = invoice_status_history.created_by', 'left')
                    ->where('invoice_id', $invoiceId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
