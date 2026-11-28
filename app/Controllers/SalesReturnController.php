<?php

namespace App\Controllers;

use App\Models\SalesReturnModel;
use App\Models\SalesReturnItemModel;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;

class SalesReturnController extends BaseController
{
    protected $returnModel;
    protected $itemModel;
    protected $invoiceModel;
    protected $customerModel;
    protected $productModel;

    public function __construct()
    {
        $this->returnModel = new SalesReturnModel();
        $this->itemModel = new SalesReturnItemModel();
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        // Overall aggregate stats
        $summaryBuilder = $this->returnModel->select('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_amount');
        if (!empty($filters['customer_id'])) {
            $summaryBuilder->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['status'])) {
            $summaryBuilder->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $summaryBuilder->where('return_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $summaryBuilder->where('return_date <=', $filters['date_to']);
        }
        $totals = $summaryBuilder->first();

        // If AJAX request asking for more rows (infinite scroll)
        if ($this->request->isAJAX() && $this->request->getGet('load_more') == 1) {
            $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));
            $limit  = max(1, (int) ($this->request->getGet('limit') ?? 20));
            $filters['offset'] = $offset;
            $filters['limit']  = $limit;

            $moreReturns = $this->returnModel->getReturnsWithFilters($filters);
            return view('sales_returns/list_rows', ['returns' => $moreReturns]);
        }

        // Default initial load: 50 credit notes
        $filters['offset'] = 0;
        $filters['limit']  = 50;

        $data['returns'] = $this->returnModel->getReturnsWithFilters($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Sales Returns (Credit Notes)';
        $data['filters'] = $filters;

        $stats = [
            'total'         => (int) ($totals['total_count'] ?? count($data['returns'])),
            'total_amount'  => (float) ($totals['total_amount'] ?? 0),
            'open_count'    => 0,
            'closed_count'  => 0,
        ];
        foreach ($data['returns'] as $r) {
            $st = $r['status'] ?? '';
            if ($st === 'Open') {
                $stats['open_count']++;
            } elseif ($st === 'Closed') {
                $stats['closed_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('sales_returns/index', $data);
    }

    private function ensureDiscountAndAdjustmentColumns(): void
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('discount_amount', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`");
        }
        if (!$db->fieldExists('discount_type', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `discount_type` VARCHAR(20) NOT NULL DEFAULT 'Fixed' AFTER `discount_amount`");
        }
        if (!$db->fieldExists('adjustment', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`");
        }
        if (!$db->fieldExists('customer_debit_note_no', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `customer_debit_note_no` VARCHAR(100) NULL AFTER `invoice_id`");
        }
        if (!$db->fieldExists('transport_name', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `transport_name` VARCHAR(150) NULL AFTER `return_date`");
        }
        if (!$db->fieldExists('bales_count', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `bales_count` INT NULL AFTER `transport_name`");
        }
        if (!$db->fieldExists('lr_number', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `lr_number` VARCHAR(100) NULL AFTER `bales_count`");
        }
        if (!$db->fieldExists('transport_amount', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `transport_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `lr_number`");
        }
        if (!$db->fieldExists('transport_paid_by', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `transport_paid_by` VARCHAR(20) NULL AFTER `lr_number`");
        }
        if (!$db->fieldExists('agent_id', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `agent_id` INT NULL AFTER `transport_paid_by`");
        }
        if (!$db->fieldExists('agent_commission_percent', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `agent_commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `agent_id`");
        }
        if (!$db->fieldExists('agent_amount_adjustment', 'sales_returns')) {
            $db->query("ALTER TABLE `sales_returns` ADD COLUMN `agent_amount_adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `adjustment`");
        }
    }

    public function create()
    {
        $this->ensureDiscountAndAdjustmentColumns();
        $data['customers'] = $this->customerModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = (new \App\Models\TaxModel())->where('status', 'Active')->orderBy('percentage', 'ASC')->findAll();
        $data['transports'] = (new \App\Models\TransportModel())->orderBy('transport_name', 'ASC')->findAll();
        $data['title'] = 'Record Sales Return';
        $data['return_number'] = $this->returnModel->generateReturnNumber();
        $data['preselected_invoice_id'] = (int) ($this->request->getGet('invoice_id') ?? 0);
        $data['preselected_customer_id'] = (int) ($this->request->getGet('customer_id') ?? 0);

        if ($data['preselected_invoice_id'] > 0 && $data['preselected_customer_id'] === 0) {
            $inv = $this->invoiceModel->find($data['preselected_invoice_id']);
            if ($inv) {
                $data['preselected_customer_id'] = (int) ($inv['customer_id'] ?? 0);
            }
        }

        return view('sales_returns/form', $data);
    }

    public function getCustomerInvoices($customerId)
    {
        $invoices = $this->invoiceModel
            ->select('id, invoice_number, invoice_date, total_amount, paid_amount, balance, status, is_inter_state')
            ->where('customer_id', (int) $customerId)
            ->where('status !=', 'Void')
            ->orderBy('invoice_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->findAll();

        return $this->response->setJSON([
            'status'   => 'success',
            'invoices' => $invoices,
        ]);
    }

    public function getInvoiceItems($invoiceId)
    {
        $invoice = $this->invoiceModel->getInvoiceById((int) $invoiceId);
        if (!$invoice) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Invoice not found.']);
        }

        $invAgentId   = !empty($invoice['agent_id']) ? (int) $invoice['agent_id'] : null;
        $invAgentPct  = (float) ($invoice['agent_commission_percent'] ?? 0);
        $invAgentName = '';
        if ($invAgentId) {
            $agentRow = (new \App\Models\AgentModel())->find($invAgentId);
            if ($agentRow) {
                $invAgentName = (string) ($agentRow['agent_name'] ?? '');
                if ($invAgentPct <= 0) {
                    $invAgentPct = (float) ($agentRow['commission_percentage'] ?? 0);
                }
            }
        }

        $items = $invoice['items'] ?? [];
        foreach ($items as &$it) {
            $pct = (float) ($it['tax_percentage'] ?? 0);
            if ($pct <= 0) {
                $pct = (float) ($it['cgst_rate'] ?? 0) + (float) ($it['sgst_rate'] ?? 0) + (float) ($it['igst_rate'] ?? 0);
            }
            if ($pct <= 0 && !empty($it['product_id'])) {
                $prod = $this->productModel->select('taxes.percentage as tax_percentage')
                    ->join('taxes', 'taxes.id = products.tax_id', 'left')
                    ->find((int) $it['product_id']);
                $pct = (float) ($prod['tax_percentage'] ?? 0);
            }
            $it['tax_percentage'] = $pct;
        }
        unset($it);

        return $this->response->setJSON([
            'status'  => 'success',
            'invoice' => [
                'id'                       => $invoice['id'],
                'invoice_number'           => $invoice['invoice_number'],
                'invoice_date'             => $invoice['invoice_date'],
                'status'                   => $invoice['status'],
                'subtotal'                 => (float) ($invoice['subtotal'] ?? 0),
                'discount_amount'          => (float) ($invoice['discount_amount'] ?? 0),
                'discount_type'            => (string) ($invoice['discount_type'] ?? 'Fixed'),
                'roundoff_amount'          => (float) ($invoice['roundoff_amount'] ?? 0),
                'total_amount'             => $invoice['total_amount'],
                'paid_amount'              => $invoice['paid_amount'],
                'balance'                  => $invoice['balance'],
                'is_inter_state'           => !empty($invoice['is_inter_state']) ? 1 : 0,
                'agent_id'                 => $invAgentId,
                'agent_name'               => $invAgentName,
                'agent_commission_percent' => $invAgentPct,
            ],
            'items'   => $items,
        ]);
    }

    public function store()
    {
        $this->ensureDiscountAndAdjustmentColumns();
        $items = $this->request->getPost('items') ?? [];
        $validItems = [];
        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $hasProdOrDesc = !empty($item['product_id']) || !empty(trim((string) ($item['description'] ?? '')));
            if ($hasProdOrDesc && $qty > 0) {
                $validItems[] = $item;
            }
        }

        if (empty($validItems)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one item with quantity greater than 0.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $returnDate = $this->request->getPost('return_date');
        $customerId = $this->request->getPost('customer_id');
        $invoiceId = $this->request->getPost('invoice_id') ?: null;
        $customReturnNo = trim((string) $this->request->getPost('return_number'));

        $balesPost = trim((string) $this->request->getPost('bales_count'));
        $paidByPost = trim((string) $this->request->getPost('transport_paid_by'));

        if ($balesPost === '' || (int) $balesPost < 1) {
            return redirect()->back()->withInput()->with('error', 'No. of Bales is required and must be at least 1.');
        }
        if (!in_array($paidByPost, ['Customer', 'Company'], true)) {
            return redirect()->back()->withInput()->with('error', 'Transport Paid By (Customer or Company) is required.');
        }

        // Auto-resolve Agent & Commission % from linked invoice or customer (no manual entry required)
        $linkedInvoice = $invoiceId ? $this->invoiceModel->find($invoiceId) : null;
        $agentId = !empty($linkedInvoice['agent_id']) ? (int) $linkedInvoice['agent_id'] : null;
        $agentPct = $linkedInvoice ? (float) ($linkedInvoice['agent_commission_percent'] ?? 0) : 0.0;

        if (!$agentId && $customerId) {
            $custRow = $this->customerModel->find($customerId);
            if (!empty($custRow['agent_id'])) {
                $agentId = (int) $custRow['agent_id'];
            }
        }
        if ($agentId && $agentPct <= 0) {
            $agentRow = (new \App\Models\AgentModel())->find($agentId);
            $agentPct = (float) ($agentRow['commission_percentage'] ?? 0);
        }

        $returnId = $this->returnModel->insert([
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'customer_debit_note_no' => trim((string) $this->request->getPost('customer_debit_note_no')) ?: null,
            'return_number' => $customReturnNo !== '' ? $customReturnNo : $this->returnModel->generateReturnNumber(),
            'return_date' => $returnDate,
            'transport_name' => trim((string) $this->request->getPost('transport_name')) ?: null,
            'bales_count' => $balesPost !== '' ? (int) $balesPost : null,
            'lr_number' => trim((string) $this->request->getPost('lr_number')) ?: null,
            'transport_amount' => (float) ($this->request->getPost('transport_amount') ?? 0),
            'transport_paid_by' => in_array($paidByPost, ['Customer', 'Company'], true) ? $paidByPost : null,
            'agent_id' => $agentId,
            'agent_commission_percent' => $agentPct,
            'agent_amount_adjustment' => (float) ($this->request->getPost('agent_amount_adjustment') ?? 0),
            'reason' => $this->request->getPost('reason'),
            'status' => 'Open',
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'created_by' => session('user_id'),
        ]);

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($validItems as $item) {
            $qty  = (float) ($item['quantity'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $taxPct = (float) ($item['tax_percentage'] ?? 0);
            $lineAmount = $qty * $rate;
            $lineTax = ($lineAmount * $taxPct) / 100;

            $desc = trim((string) ($item['description'] ?? ''));
            if ($desc === '' && !empty($item['product_id'])) {
                $prod = $this->productModel->find((int) $item['product_id']);
                $desc = $prod['product_name'] ?? '';
            }

            $this->itemModel->insert([
                'sales_return_id' => $returnId,
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $desc,
                'quantity' => $qty,
                'rate' => $rate,
                'tax_percentage' => $taxPct,
                'amount' => $lineAmount
            ]);

            $subtotal += $lineAmount;
            $taxAmount += $lineTax;
        }

        $includeDiscount = !empty($this->request->getPost('include_discount'));
        $rawDiscount     = $includeDiscount ? max(0.0, (float) ($this->request->getPost('discount_amount') ?? 0)) : 0.0;
        $discountType    = ($this->request->getPost('discount_type') === 'Percentage') ? 'Percentage' : 'Fixed';
        $adjustment      = (float) ($this->request->getPost('adjustment') ?? 0);

        $discountValue = ($discountType === 'Percentage')
            ? round(($subtotal * $rawDiscount) / 100, 2)
            : min($subtotal, $rawDiscount);

        $taxFactor = ($subtotal > 0 && $discountValue > 0)
            ? max(0.0, ($subtotal - $discountValue) / $subtotal)
            : 1.0;
        $effectiveTax = round($taxAmount * $taxFactor, 2);
        $totalAmount  = round(max(0.0, $subtotal - $discountValue) + $effectiveTax + $adjustment, 2);

        $agentAmountAdjustment = ($agentId && $agentPct > 0)
            ? round((max(0.0, $subtotal - $discountValue) * $agentPct) / 100, 2)
            : 0.0;

        $returnUpdate = [
            'subtotal'                => $subtotal,
            'discount_amount'         => $rawDiscount,
            'discount_type'           => $discountType,
            'tax_amount'              => $effectiveTax,
            'adjustment'              => $adjustment,
            'agent_amount_adjustment' => $agentAmountAdjustment,
            'total_amount'            => $totalAmount,
        ];

        // Apply Credit Note directly to linked Invoice (update invoice paid_amount, balance, status)
        if ($linkedInvoice && ($linkedInvoice['status'] ?? '') !== 'Void') {
            $invId = (int) $linkedInvoice['id'];
            $invBeforeBalance = (float) ($linkedInvoice['balance'] ?? 0);

            if ($invBeforeBalance > 0 && $totalAmount > 0) {
                $returnUpdate['status'] = 'Closed';
            }

            $this->returnModel->update($returnId, $returnUpdate);

            $payRow = $db->table('invoice_payments')
                ->select('COALESCE(SUM(amount + COALESCE(discount_amount,0) + COALESCE(mahimai_amount,0) + COALESCE(postal_charges,0)), 0) as total_paid')
                ->where('invoice_id', $invId)
                ->get()->getRowArray();
            $retRow = $db->table('sales_returns')
                ->select('COALESCE(SUM(total_amount), 0) as total_returns')
                ->where('invoice_id', $invId)
                ->where('status !=', 'Void')
                ->get()->getRowArray();

            $totalDeductions = (float) ($payRow['total_paid'] ?? 0) + (float) ($retRow['total_returns'] ?? 0);
            $invTotal        = (float) ($linkedInvoice['total_amount'] ?? 0);
            $newBalance      = max(0.0, round($invTotal - $totalDeductions, 2));
            $newStatus       = $linkedInvoice['status'];
            if ($newStatus !== 'Draft') {
                if ($newBalance <= 0.009) {
                    $newStatus = 'Paid';
                } elseif ($totalDeductions > 0) {
                    $newStatus = 'Partially Paid';
                } else {
                    $newStatus = 'Open';
                }
            }

            $invUpdate = [
                'paid_amount' => round($totalDeductions, 2),
                'balance'     => $newBalance,
                'status'      => $newStatus,
            ];

            // Automatically reduce unpaid agent commission amount on the linked invoice
            if ($agentAmountAdjustment > 0 && ($linkedInvoice['agent_commission_status'] ?? '') === 'Unpaid') {
                $currentInvComm = (float) ($linkedInvoice['agent_commission_amount'] ?? 0);
                if ($currentInvComm > 0) {
                    $invUpdate['agent_commission_amount'] = max(0.0, round($currentInvComm - $agentAmountAdjustment, 2));
                }
            }

            $this->invoiceModel->update($invId, $invUpdate);
        } else {
            $this->returnModel->update($returnId, $returnUpdate);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save return.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('credit_notes', (int) $returnId, 'CREATE');
        }

        return redirect()->to('sales_returns')->with('success', 'Sales return recorded successfully.');
    }

    public function view($id)
    {
        $data['return'] = $this->returnModel->getReturnById($id);

        if (!$data['return']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Return not found.']);
            }
            return redirect()->to('sales_returns')->with('error', 'Return not found.');
        }

        $data['items'] = $data['return']['items'] ?? [];
        $data['title'] = 'Sales Return Details - ' . $data['return']['return_number'];

        // AJAX request for side panel view
        if ($this->request->isAJAX() || $this->request->getGet('ajax') == 1) {
            return view('sales_returns/detail_pane', $data);
        }

        $data['is_single'] = ($this->request->getGet('mode') === 'single');

        return view('sales_returns/view', $data);
    }

    public function print($id)
    {
        $data['return'] = $this->returnModel->getReturnById($id);
        if (!$data['return']) {
            return redirect()->to('sales_returns')->with('error', 'Credit note not found.');
        }

        $data['items'] = $data['return']['items'] ?? [];
        $data['title'] = 'Credit Note ' . $data['return']['return_number'];

        if ($this->request->getGet('format') !== 'html') {
            try {
                $html = view('sales_returns/print', array_merge($data, ['auto_print' => false]));
                return \App\Libraries\PdfRenderer::streamDocument('credit_note', $html, 'CreditNote_' . $data['return']['return_number']);
            } catch (\Throwable $e) {
                log_message('error', 'Credit Note PDF generation error: ' . $e->getMessage());
            }
        }

        return view('sales_returns/print', $data);
    }
}
