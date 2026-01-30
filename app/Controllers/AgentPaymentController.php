<?php

namespace App\Controllers;

use App\Models\AgentPaymentModel;
use App\Models\AgentPaymentItemModel;
use App\Models\AgentModel;
use App\Models\InvoiceModel;
use App\Models\BankAccountModel;

class AgentPaymentController extends BaseController
{
    protected $paymentModel;
    protected $itemModel;
    protected $agentModel;
    protected $invoiceModel;
    protected $bankModel;

    protected $accountingModel;

    public function __construct()
    {
        $this->paymentModel = new AgentPaymentModel();
        $this->itemModel = new AgentPaymentItemModel();
        $this->agentModel = new AgentModel();
        $this->invoiceModel = new InvoiceModel();
        $this->bankModel = new BankAccountModel();
        $this->accountingModel = new \App\Models\AccountingModel();
    }

    /**
     * List all agent payments
     */
    public function datatable()
    {
        $request = \Config\Services::request();
        
        $start = $request->getGet('start');
        $length = $request->getGet('length');
        $search = $request->getGet('search')['value'] ?? '';
        $order = $request->getGet('order');

        $data = $this->paymentModel->getDatatablePayments($start, $length, $search, $order);
        $totalRecords = $this->paymentModel->countAll();
        $filteredRecords = $this->paymentModel->countDatatableFiltered($search);
        
        // Format data for DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                '<strong>' . esc($row['payment_number']) . '</strong>',
                date('d/m/Y', strtotime($row['payment_date'])),
                esc($row['agent_name']),
                esc($row['phone_number']),
                '<span class="badge text-bg-info">' . esc($row['payment_mode']) . '</span>',
                esc($row['reference_number']) ?: '-',
                '<div class="text-end fw-bold">₹' . number_format($row['amount'], 2) . '</div>',
                '<div class="text-center">
                    <div class="btn-group btn-group-sm">
                        <a href="' . site_url('agent-payments/view/' . $row['id']) . '" class="btn btn-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="' . site_url('agent-payments/delete/' . $row['id']) . '" class="btn btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete"><i class="fas fa-trash"></i></a>
                    </div>
                 </div>'
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($request->getGet('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    public function index()
    {
        $limit = 25;
        $offset = intval($this->request->getGet('offset') ?? 0);
        
        $filters = [
            'agent_id'     => $this->request->getGet('agent_id'),
            'payment_mode' => $this->request->getGet('payment_mode'),
            'date_from'    => $this->request->getGet('date_from'),
            'date_to'      => $this->request->getGet('date_to'),
            'search'       => $this->request->getGet('search'),
        ];

        $builder = $this->paymentModel->builder();
        $builder->select('agent_payments.*, agents.agent_name, agents.phone_number')
                ->join('agents', 'agents.id = agent_payments.agent_id', 'left');

        if (!empty($filters['agent_id'])) {
            $builder->where('agent_payments.agent_id', $filters['agent_id']);
        }
        if (!empty($filters['payment_mode'])) {
            $builder->where('agent_payments.payment_mode', $filters['payment_mode']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('agent_payments.payment_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('agent_payments.payment_date <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('agent_payments.payment_number', $filters['search'])
                    ->orLike('agent_payments.reference_number', $filters['search'])
                    ->groupEnd();
        }

        $countBuilder = clone $builder;
        $totalResults = $countBuilder->countAllResults(false);

        $payments = $builder->orderBy('agent_payments.payment_date', 'DESC')
                            ->orderBy('agent_payments.id', 'DESC')
                            ->get($limit, $offset)
                            ->getResultArray();

        $data = [
            'title'        => 'Agent Payments',
            'payments'     => $payments,
            'agents'       => $this->agentModel->orderBy('agent_name', 'ASC')->findAll(),
            'total_count'  => $totalResults,
            'filters'      => $filters,
            'limit'        => $limit,
            'offset'       => $offset,
            'has_more'     => ($offset + $limit) < $totalResults
        ];

        if ($this->request->isAJAX()) {
            return view('agent_payments/rows', $data);
        }
        
        return view('agent_payments/index', $data);
    }

    /**
     * Show payment form
     */
    public function create($agentId = null)
    {
        $data['agents'] = $this->agentModel->orderBy('agent_name', 'ASC')->findAll();
        $data['bank_accounts'] = $this->bankModel->where('status', 'Active')->findAll();
        $data['payment_number'] = $this->paymentModel->generatePaymentNumber();
        $data['title'] = 'Record Agent Payment';
        $data['payment'] = null;
        $data['selected_agent_id'] = $agentId;

        // If agent is pre-selected, load pending commissions
        if ($agentId) {
            $data['pending_commissions'] = $this->paymentModel->getUnpaidCommissions($agentId);
        }

        return view('agent_payments/form', $data);
    }

    /**
     * Store new payment
     */
    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $paymentData = [
            'payment_number'   => $this->request->getPost('payment_number'),
            'agent_id'         => $this->request->getPost('agent_id'),
            'payment_date'     => $this->request->getPost('payment_date'),
            'payment_mode'     => $this->request->getPost('payment_mode'),
            'bank_account_id'  => $this->request->getPost('bank_account_id') ?: null,
            'amount'           => $this->request->getPost('amount'),
            'reference_number' => $this->request->getPost('reference_number'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => session('user_id'),
        ];

        if (!$this->paymentModel->insert($paymentData)) {
            return redirect()->back()->withInput()->with('errors', $this->paymentModel->errors());
        }

        $paymentId = $this->paymentModel->getInsertID();

        // --- ACCOUNTING LEDGER ---
        $paymentDate = $paymentData['payment_date'];
        $agent = $this->agentModel->find($paymentData['agent_id']);
        $agentName = $agent ? $agent['agent_name'] : 'Unknown';
        $paymentRef = "Agent Payment: " . $paymentData['payment_number'] . " to $agentName";

        // 1. Dr Agent Commission Expense (Expense increases)
        $this->accountingModel->postEntry('Agent Commission Expense', $paymentDate, $paymentData['amount'], 0, $paymentRef, 'agent_payment', $paymentId);

        // 2. Cr Bank/Cash (Asset decreases)
        $paymentAccount = ($paymentData['payment_mode'] == 'Cash') ? 'Cash' : 'Bank Account';
        $this->accountingModel->postEntry($paymentAccount, $paymentDate, 0, $paymentData['amount'], $paymentRef, 'agent_payment', $paymentId);
        // ------------------------

        $invoiceIds = $this->request->getPost('invoice_ids') ?? [];

        // Insert payment items and update invoice commission status
        foreach ($invoiceIds as $invoiceId) {
            $invoice = $this->invoiceModel->find($invoiceId);
            
            if ($invoice && $invoice['agent_commission_status'] == 'Unpaid') {
                // Insert payment item
                $this->itemModel->insert([
                    'agent_payment_id'  => $paymentId,
                    'invoice_id'        => $invoiceId,
                    'commission_amount' => $invoice['agent_commission_amount'],
                ]);

                // Update invoice commission status
                $this->invoiceModel->update($invoiceId, [
                    'agent_commission_status'  => 'Paid',
                    'agent_commission_paid_at' => $paymentData['payment_date'],
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to record payment.');
        }

        return redirect()->to('agent-payments/view/' . $paymentId)->with('success', 'Payment recorded successfully.');
    }

    /**
     * View payment details
     */
    public function view($id)
    {
        $data['payment'] = $this->paymentModel->getPaymentById($id);
        
        if (!$data['payment']) {
            return redirect()->to('agent-payments')->with('error', 'Payment not found.');
        }

        $data['title'] = 'Payment Details - ' . $data['payment']['payment_number'];

        return view('agent_payments/view', $data);
    }

    /**
     * Delete payment
     */
    public function delete($id)
    {
        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            return redirect()->to('agent-payments')->with('error', 'Payment not found.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Get payment items to revert invoice status
        $items = $this->itemModel->where('agent_payment_id', $id)->findAll();
        
        foreach ($items as $item) {
            $this->invoiceModel->update($item['invoice_id'], [
                'agent_commission_status'  => 'Unpaid',
                'agent_commission_paid_at' => null,
            ]);
        }

        // Delete payment items
        $this->itemModel->where('agent_payment_id', $id)->delete();

        // Delete payment
        $this->paymentModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete payment.');
        }

        return redirect()->to('agent-payments')->with('success', 'Payment deleted successfully.');
    }

    /**
     * Get pending commissions for an agent (AJAX)
     */
    public function getPendingCommissions($agentId)
    {
        $commissions = $this->paymentModel->getUnpaidCommissions($agentId);
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $commissions
        ]);
    }

    /**
     * Commission reports
     */
    public function reports()
    {
        $filters = [
            'agent_id'  => $this->request->getGet('agent_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $data['summary'] = $this->paymentModel->getCommissionSummary($filters);
        $data['agents'] = $this->agentModel->orderBy('agent_name', 'ASC')->findAll();
        $data['title'] = 'Waiting Payment Reports';
        $data['filters'] = $filters;

        return view('agent_payments/reports', $data);
    }

    public function previewReport()
    {
        $ids = $this->request->getGet('ids');
        $agentId = $this->request->getGet('agent_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        $builder = $this->invoiceModel
            ->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id', 'left');

        if (!empty($ids)) {
            $idArray = explode(',', $ids);
            $builder->whereIn('invoices.id', $idArray);
        } elseif (!empty($agentId)) {
            $builder->where('invoices.agent_id', $agentId)
                    ->where('invoices.agent_commission_status', 'Unpaid')
                    ->where('invoices.agent_commission_amount >', 0);
            
            if ($dateFrom) $builder->where('invoices.invoice_date >=', $dateFrom);
            if ($dateTo) $builder->where('invoices.invoice_date <=', $dateTo);
        } else {
            return "No criteria provided for report.";
        }
        
        $data['invoices'] = $builder->findAll();

        if (empty($data['invoices'])) {
            return "No matching unpaid invoices found for the selected criteria.";
        }

        $finalAgentId = $agentId ?: $data['invoices'][0]['agent_id'];
        $data['agent'] = $this->agentModel->find($finalAgentId);
        
        return view('agent_payments/preview_report', $data);
    }
}
