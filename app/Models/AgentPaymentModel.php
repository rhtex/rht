<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentPaymentModel extends Model
{
    protected $table            = 'agent_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'payment_number', 'agent_id', 'payment_date', 'payment_mode',
        'bank_account_id', 'bank_transaction_id', 'amount', 'reference_number', 'notes',
        'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'agent_id'      => 'required|integer',
        'payment_date'  => 'required|valid_date',
        'payment_mode'  => 'required|in_list[Cash,Bank Transfer,Cheque,UPI,Other]',
        'amount'        => 'required|decimal|greater_than[0]',
    ];

    /**
     * Get payments with agent details
     */
    public function getDatatablePayments($start, $length, $search, $order)
    {
        $builder = $this->builder();
        $builder->select('agent_payments.*, agents.agent_name, agents.phone_number')
                ->join('agents', 'agents.id = agent_payments.agent_id', 'left');

        // Search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('agent_payments.payment_number', $search)
                ->orLike('agents.agent_name', $search)
                ->orLike('agent_payments.payment_mode', $search)
                ->orLike('agent_payments.reference_number', $search)
                ->groupEnd();
        }

        // Sorting
        $columns = [
            0 => 'agent_payments.payment_number',
            1 => 'agent_payments.payment_date',
            2 => 'agents.agent_name',
            3 => 'agents.phone_number',
            4 => 'agent_payments.payment_mode',
            5 => 'agent_payments.reference_number',
            6 => 'agent_payments.amount'
        ];

        if (isset($order[0]['column']) && isset($columns[$order[0]['column']])) {
            $builder->orderBy($columns[$order[0]['column']], $order[0]['dir']);
        } else {
            $builder->orderBy('agent_payments.payment_date', 'DESC');
        }

        // Pagination
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countDatatableFiltered($search)
    {
        $builder = $this->builder();
        $builder->join('agents', 'agents.id = agent_payments.agent_id', 'left');
        
        if (!empty($search)) {
            $builder->groupStart()
                ->like('agent_payments.payment_number', $search)
                ->orLike('agents.agent_name', $search)
                ->orLike('agent_payments.payment_mode', $search)
                ->orLike('agent_payments.reference_number', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getPaymentsWithAgent($filters = [], $limit = 0, $offset = 0)
    {
        // Use the builder directly to avoid Model state issues
        $builder = $this->builder();
        $builder->select('agent_payments.*, agents.agent_name, agents.phone_number')
                ->join('agents', 'agents.id = agent_payments.agent_id', 'left');

        if (!empty($filters['agent_id'])) {
            $builder->where('agent_payments.agent_id', $filters['agent_id']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('agent_payments.payment_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('agent_payments.payment_date <=', $filters['date_to']);
        }

        if (!empty($filters['payment_mode'])) {
            $builder->where('agent_payments.payment_mode', $filters['payment_mode']);
        }

        $builder->orderBy('agent_payments.payment_date', 'DESC')
                ->orderBy('agent_payments.id', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get payment by ID with items and invoices
     */
    public function getPaymentById($id)
    {
        $payment = $this->select('agent_payments.*, agents.agent_name, agents.phone_number, 
                                  bank_accounts.bank_name as account_name, bank_accounts.account_number')
                       ->join('agents', 'agents.id = agent_payments.agent_id', 'left')
                       ->join('bank_accounts', 'bank_accounts.id = agent_payments.bank_account_id', 'left')
                       ->find($id);

        if ($payment) {
            // Get payment items with invoice details
            $itemModel = new \App\Models\AgentPaymentItemModel();
            $payment['items'] = $itemModel->select('agent_payment_items.*, 
                                                     invoices.invoice_number, 
                                                     invoices.invoice_date,
                                                     invoices.subtotal,
                                                     invoices.tax_amount,
                                                     invoices.total_amount as invoice_total,
                                                     customers.name as customer_name')
                                          ->join('invoices', 'invoices.id = agent_payment_items.invoice_id', 'left')
                                          ->join('customers', 'customers.id = invoices.customer_id', 'left')
                                          ->where('agent_payment_items.agent_payment_id', $id)
                                          ->findAll();
        }

        return $payment;
    }

    /**
     * Generate next payment number
     */
    public function generatePaymentNumber()
    {
        $prefix = 'AGPAY-' . date('Ym') . '-';
        $lastPayment = $this->like('payment_number', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment['payment_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get unpaid commissions for an agent
     */
    public function getUnpaidCommissions($agentId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('invoices')
                  ->select('invoices.id, invoices.invoice_number, invoices.invoice_date, 
                           invoices.subtotal, invoices.tax_amount, invoices.total_amount, 
                           invoices.agent_commission_percent, invoices.agent_commission_amount, 
                           customers.name as customer_name')
                  ->join('customers', 'customers.id = invoices.customer_id', 'left')
                  ->where('invoices.agent_id', $agentId)
                  ->where('invoices.agent_commission_status', 'Unpaid')
                  ->where('invoices.agent_commission_amount >', 0)
                  ->orderBy('invoices.invoice_date', 'ASC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get commission summary by agent
     */
    public function getCommissionSummary($filters = [])
    {
        $db = \Config\Database::connect();
        $builder = $db->table('agents')
                     ->select('agents.id, agents.agent_name, agents.commission_percentage,
                              COUNT(DISTINCT invoices.id) as total_invoices,
                              COUNT(DISTINCT CASE WHEN invoices.agent_commission_status = "Unpaid" THEN invoices.id END) as pending_invoices_count,
                              COALESCE(SUM(CASE WHEN invoices.agent_commission_status = "Unpaid" THEN invoices.agent_commission_amount ELSE 0 END), 0) as pending_commission,
                              COALESCE(SUM(CASE WHEN invoices.agent_commission_status = "Paid" THEN invoices.agent_commission_amount ELSE 0 END), 0) as paid_commission,
                              COALESCE(SUM(invoices.agent_commission_amount), 0) as total_commission')
                     ->join('invoices', 'invoices.agent_id = agents.id', 'left')
                     ->groupBy('agents.id');

        if (!empty($filters['agent_id'])) {
            $builder->where('agents.id', $filters['agent_id']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('invoices.invoice_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('invoices.invoice_date <=', $filters['date_to']);
        }

        return $builder->get()->getResultArray();
    }
}
