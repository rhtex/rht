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

    public function __construct()
    {
        $this->paymentModel = new AgentPaymentModel();
        $this->itemModel = new AgentPaymentItemModel();
        $this->agentModel = new AgentModel();
        $this->invoiceModel = new InvoiceModel();
        $this->bankModel = new BankAccountModel();
    }

    /**
     * List all agent payments
     */
    public function index()
    {
        $filters = [
            'agent_id'     => $this->request->getGet('agent_id'),
            'date_from'    => $this->request->getGet('date_from'),
            'date_to'      => $this->request->getGet('date_to'),
            'payment_mode' => $this->request->getGet('payment_mode'),
        ];

        $data['payments'] = $this->paymentModel->getPaymentsWithAgent($filters);
        $data['agents'] = $this->agentModel->findAll();
        $data['title'] = 'Agent Payments';
        $data['filters'] = $filters;

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
