<?php

namespace App\Controllers;

use App\Models\QuotationModel;
use App\Models\QuotationItemModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\TransportModel;

class QuotationController extends BaseController
{
    protected $quotationModel;
    protected $itemModel;
    protected $customerModel;
    protected $productModel;
    protected $taxModel;
    protected $agentModel;
    protected $transportModel;

    public function __construct()
    {
        $this->quotationModel = new QuotationModel();
        $this->itemModel = new QuotationItemModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->taxModel = new \App\Models\TaxModel();
        $this->agentModel = new \App\Models\AgentModel();
        $this->transportModel = new TransportModel();
    }

    public function index()
    {
        $filters = [
            'quotation_number' => $this->request->getGet('quotation_number'),
            'customer_id'      => $this->request->getGet('customer_id'),
            'status'           => $this->request->getGet('status'),
            'date_from'        => $this->request->getGet('date_from'),
            'date_to'          => $this->request->getGet('date_to'),
        ];

        // Overall Aggregate stats
        $summaryBuilder = $this->quotationModel->select('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_amount');
        if (!empty($filters['quotation_number'])) {
            $summaryBuilder->like('quotation_number', trim($filters['quotation_number']));
        }
        if (!empty($filters['customer_id'])) {
            $summaryBuilder->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['status'])) {
            $summaryBuilder->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $summaryBuilder->where('quotation_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $summaryBuilder->where('quotation_date <=', $filters['date_to']);
        }
        $totals = $summaryBuilder->first();

        // If AJAX request asking for more rows (infinite scroll)
        if ($this->request->isAJAX() && $this->request->getGet('load_more') == 1) {
            $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));
            $limit  = max(1, (int) ($this->request->getGet('limit') ?? 20));
            $filters['offset'] = $offset;
            $filters['limit']  = $limit;

            $moreQuotations = $this->quotationModel->getQuotationsWithCustomer($filters);
            return view('quotations/list_rows', ['quotations' => $moreQuotations]);
        }

        // Default initial load: 50 quotations
        $filters['offset'] = 0;
        $filters['limit']  = 50;

        $data['quotations'] = $this->quotationModel->getQuotationsWithCustomer($filters);
        $data['customers']  = $this->customerModel->where('status', 'active')->findAll();
        $data['filters']    = $filters;
        $data['title']      = 'Quotations / Estimates';

        $stats = [
            'total'          => (int) ($totals['total_count'] ?? count($data['quotations'])),
            'total_amount'   => (float) ($totals['total_amount'] ?? 0),
            'accepted_count' => 0,
            'invoiced_count' => 0,
            'sent_count'     => 0,
            'draft_count'    => 0,
        ];
        foreach ($data['quotations'] as $q) {
            $st = $q['status'] ?? '';
            if ($st === 'Accepted') {
                $stats['accepted_count']++;
            } elseif ($st === 'Invoiced') {
                $stats['invoiced_count']++;
            } elseif ($st === 'Sent') {
                $stats['sent_count']++;
            } elseif ($st === 'Draft') {
                $stats['draft_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('quotations/index', $data);
    }

    public function create()
    {
        $data['customers']        = $this->customerModel->where('status', 'active')->findAll();
        $data['products']         = $this->productModel->getProductsWithCategory();
        $data['taxes']            = $this->taxModel->where('status', 'Active')->findAll();
        $data['agents']           = $this->agentModel->findAll();
        $data['transports']       = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['quotation_number'] = $this->quotationModel->generateQuotationNumber();
        $data['title']            = 'Create Quotation / Estimate';
        $data['quotation']        = null;

        return view('quotations/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $rawItems = $this->request->getPost('items') ?? [];
        $items = [];
        foreach ($rawItems as $item) {
            if (!empty($item['description']) || !empty($item['product_id'])) {
                $qty = (float) ($item['quantity'] ?? 0);
                $rate = (float) ($item['rate'] ?? 0);
                if ($qty > 0 && $rate >= 0) {
                    $items[] = $item;
                }
            }
        }

        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one valid line item with quantity and rate.');
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['quantity'] * (float) $item['rate']);
        }

        $discountAmount = (float) ($this->request->getPost('discount_amount') ?? 0);
        $discountType = $this->request->getPost('discount_type') ?? 'Fixed';
        $totalDiscount = ($discountType == 'Percentage') ? ($subtotal * $discountAmount / 100) : $discountAmount;

        $taxAmount = 0;
        foreach ($items as $item) {
            $itemAmt = (float) $item['quantity'] * (float) $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
            $taxableVal = max(0, $itemAmt - $itemDiscount);
            $taxAmount += ($taxableVal * ((float) ($item['tax_percentage'] ?? 0)) / 100);
        }

        $shippingCharge = (float) ($this->request->getPost('shipping_charge') ?? 0);
        $roundoffAmount = (float) ($this->request->getPost('roundoff_amount') ?? 0);
        $totalAmount = max(0, ($subtotal - $totalDiscount) + $taxAmount + $shippingCharge + $roundoffAmount);

        $agentId = $this->request->getPost('agent_id') ?: null;
        $agentCommissionPercent = (float) ($this->request->getPost('agent_commission_percent') ?? 0);
        $agentCommissionAmount = ($agentId && $agentCommissionPercent > 0) ? ($subtotal * $agentCommissionPercent / 100) : 0;

        $quotationData = [
            'customer_id'              => $this->request->getPost('customer_id'),
            'agent_id'                 => $agentId,
            'agent_commission_percent' => $agentCommissionPercent,
            'agent_commission_amount'  => $agentCommissionAmount,
            'quotation_number'         => $this->request->getPost('quotation_number'),
            'quotation_date'           => $this->request->getPost('quotation_date'),
            'expiry_date'              => $this->request->getPost('expiry_date') ?: null,
            'reference_number'         => $this->request->getPost('reference_number'),
            'transport_name'           => $this->request->getPost('transport_name'),
            'waybill_number'           => $this->request->getPost('waybill_number'),
            'notes'                    => $this->request->getPost('notes'),
            'terms'                    => $this->request->getPost('terms'),
            'subtotal'                 => $subtotal,
            'discount_amount'          => $discountAmount,
            'discount_type'            => $discountType,
            'tax_amount'               => $taxAmount,
            'shipping_charge'          => $shippingCharge,
            'roundoff_amount'          => $roundoffAmount,
            'total_amount'             => $totalAmount,
            'is_inter_state'           => $this->request->getPost('is_inter_state') ? 1 : 0,
            'created_by'               => session('user_id'),
            'status'                   => 'Draft'
        ];

        if (!$this->quotationModel->insert($quotationData)) {
            return redirect()->back()->withInput()->with('errors', $this->quotationModel->errors());
        }

        $quotationId = $this->quotationModel->getInsertID();

        foreach ($items as $item) {
            $this->itemModel->insert([
                'quotation_id'   => $quotationId,
                'product_id'     => !empty($item['product_id']) ? $item['product_id'] : null,
                'description'    => $item['description'],
                'hsn_code'       => $item['hsn_code'] ?? null,
                'quantity'       => (float) $item['quantity'],
                'rate'           => (float) $item['rate'],
                'tax_percentage' => (float) ($item['tax_percentage'] ?? 0),
                'amount'         => (float) $item['quantity'] * (float) $item['rate']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create quotation.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('quotations', (int) $quotationId, 'CREATE');
        }

        return redirect()->to('quotations/view/' . $quotationId)->with('success', 'Quotation created successfully.');
    }

    public function view($id)
    {
        $data['quotation'] = $this->quotationModel->getQuotationById($id);
        if (!$data['quotation']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Quotation not found.']);
            }
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        $data['title'] = 'Quotation #' . $data['quotation']['quotation_number'];

        // AJAX request for side panel view
        if ($this->request->isAJAX() || $this->request->getGet('ajax') == 1) {
            return view('quotations/detail_pane', $data);
        }

        $data['is_single'] = ($this->request->getGet('mode') === 'single');

        return view('quotations/view', $data);
    }

    public function edit($id)
    {
        $data['quotation'] = $this->quotationModel->getQuotationById($id);
        if (!$data['quotation']) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        $isConverted = !empty($data['quotation']['converted_to_so_id']) || !empty($data['quotation']['converted_to_invoice_id']) || !empty($data['quotation']['linked_so_id']) || in_array($data['quotation']['status'], ['Accepted', 'Invoiced']);
        if ($isConverted) {
            return redirect()->to('quotations/view/' . $id)->with('error', 'Cannot edit a quotation that has already been converted.');
        }

        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['title'] = 'Edit Quotation #' . $data['quotation']['quotation_number'];

        return view('quotations/form', $data);
    }

    public function update($id)
    {
        $quotation = $this->quotationModel->find($id);
        if (!$quotation) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        if (!empty($quotation['converted_to_so_id']) || !empty($quotation['converted_to_invoice_id']) || in_array($quotation['status'], ['Accepted', 'Invoiced'])) {
            return redirect()->to('quotations/view/' . $id)->with('error', 'Cannot edit a quotation that has already been converted.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $rawItems = $this->request->getPost('items') ?? [];
        $items = [];
        foreach ($rawItems as $item) {
            if (!empty($item['description']) || !empty($item['product_id'])) {
                $qty = (float) ($item['quantity'] ?? 0);
                $rate = (float) ($item['rate'] ?? 0);
                if ($qty > 0 && $rate >= 0) {
                    $items[] = $item;
                }
            }
        }

        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one valid line item with quantity and rate.');
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['quantity'] * (float) $item['rate']);
        }

        $discountAmount = (float) ($this->request->getPost('discount_amount') ?? 0);
        $discountType = $this->request->getPost('discount_type') ?? 'Fixed';
        $totalDiscount = ($discountType == 'Percentage') ? ($subtotal * $discountAmount / 100) : $discountAmount;

        $taxAmount = 0;
        foreach ($items as $item) {
            $itemAmt = (float) $item['quantity'] * (float) $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
            $taxableVal = max(0, $itemAmt - $itemDiscount);
            $taxAmount += ($taxableVal * ((float) ($item['tax_percentage'] ?? 0)) / 100);
        }

        $shippingCharge = (float) ($this->request->getPost('shipping_charge') ?? 0);
        $roundoffAmount = (float) ($this->request->getPost('roundoff_amount') ?? 0);
        $totalAmount = max(0, ($subtotal - $totalDiscount) + $taxAmount + $shippingCharge + $roundoffAmount);

        $agentId = $this->request->getPost('agent_id') ?: null;
        $agentCommissionPercent = (float) ($this->request->getPost('agent_commission_percent') ?? 0);
        $agentCommissionAmount = ($agentId && $agentCommissionPercent > 0) ? ($subtotal * $agentCommissionPercent / 100) : 0;

        $quotationData = [
            'customer_id'              => $this->request->getPost('customer_id'),
            'agent_id'                 => $agentId,
            'agent_commission_percent' => $agentCommissionPercent,
            'agent_commission_amount'  => $agentCommissionAmount,
            'quotation_date'           => $this->request->getPost('quotation_date'),
            'expiry_date'              => $this->request->getPost('expiry_date') ?: null,
            'reference_number'         => $this->request->getPost('reference_number'),
            'transport_name'           => $this->request->getPost('transport_name'),
            'waybill_number'           => $this->request->getPost('waybill_number'),
            'notes'                    => $this->request->getPost('notes'),
            'terms'                    => $this->request->getPost('terms'),
            'subtotal'                 => $subtotal,
            'discount_amount'          => $discountAmount,
            'discount_type'            => $discountType,
            'tax_amount'               => $taxAmount,
            'shipping_charge'          => $shippingCharge,
            'roundoff_amount'          => $roundoffAmount,
            'total_amount'             => $totalAmount,
            'is_inter_state'           => $this->request->getPost('is_inter_state') ? 1 : 0,
            'updated_by'               => session('user_id'),
        ];

        $this->quotationModel->update($id, $quotationData);

        $this->itemModel->where('quotation_id', $id)->delete();
        foreach ($items as $item) {
            $this->itemModel->insert([
                'quotation_id'   => $id,
                'product_id'     => !empty($item['product_id']) ? $item['product_id'] : null,
                'description'    => $item['description'],
                'hsn_code'       => $item['hsn_code'] ?? null,
                'quantity'       => (float) $item['quantity'],
                'rate'           => (float) $item['rate'],
                'tax_percentage' => (float) ($item['tax_percentage'] ?? 0),
                'amount'         => (float) $item['quantity'] * (float) $item['rate']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update quotation.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('quotations', (int) $id, 'UPDATE');
        }

        return redirect()->to('quotations/view/' . $id)->with('success', 'Quotation updated successfully.');
    }

    public function delete($id)
    {
        $quotation = $this->quotationModel->find($id);
        if (!$quotation) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        if (!empty($quotation['converted_to_so_id']) || !empty($quotation['converted_to_invoice_id']) || in_array($quotation['status'], ['Accepted', 'Invoiced'])) {
            return redirect()->to('quotations/view/' . $id)->with('error', 'Cannot delete a quotation that has already been converted.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('quotations', (int) $id, 'DELETE');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $this->itemModel->where('quotation_id', $id)->delete();
        $this->quotationModel->delete($id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('quotations')->with('error', 'Failed to delete quotation.');
        }

        return redirect()->to('quotations')->with('success', 'Quotation deleted successfully.');
    }

    public function convertToSalesOrder($id)
    {
        $quotation = $this->quotationModel->getQuotationById($id);
        if (!$quotation) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        $soId = $quotation['converted_to_so_id'] ?: ($quotation['linked_so_id'] ?? null);
        if (!empty($soId)) {
            return redirect()->to('sales_orders/view/' . $soId)
                ->with('info', 'This quotation has already been converted to Sales Order ' . (!empty($quotation['converted_so_number']) ? '#' . $quotation['converted_so_number'] : '') . '.');
        }

        if (!empty($quotation['converted_to_invoice_id'])) {
            return redirect()->to('invoices/view/' . $quotation['converted_to_invoice_id'])
                ->with('info', 'This quotation has already been converted to an Invoice.');
        }

        if (in_array($quotation['status'], ['Accepted', 'Invoiced'])) {
            return redirect()->to('quotations/view/' . $id)
                ->with('error', 'This quotation has already been converted and cannot be converted again.');
        }

        return redirect()->to('sales_orders/create?quotation_id=' . $id);
    }

    public function convertToInvoice($id)
    {
        $quotation = $this->quotationModel->getQuotationById($id);
        if (!$quotation) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        if (!empty($quotation['converted_to_invoice_id'])) {
            return redirect()->to('invoices/view/' . $quotation['converted_to_invoice_id'])
                ->with('info', 'This quotation has already been converted to Invoice ' . (!empty($quotation['converted_invoice_number']) ? '#' . $quotation['converted_invoice_number'] : '') . '.');
        }

        $soId = $quotation['converted_to_so_id'] ?: ($quotation['linked_so_id'] ?? null);
        if (!empty($soId)) {
            return redirect()->to('sales_orders/view/' . $soId)
                ->with('info', 'This quotation has already been converted to a Sales Order.');
        }

        if (in_array($quotation['status'], ['Accepted', 'Invoiced'])) {
            return redirect()->to('quotations/view/' . $id)
                ->with('error', 'This quotation has already been converted and cannot be converted again.');
        }

        return redirect()->to('invoices/create?quotation_id=' . $id);
    }

    public function print($id)
    {
        $data['quotation'] = $this->quotationModel->getQuotationById($id);
        if (!$data['quotation']) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        $data['company_name']    = get_setting('app_name', 'RasiDev');
        $data['company_address'] = get_setting('company_address', '');
        $data['company_gstin']   = get_setting('company_gstin', '');
        $data['company_contact'] = get_setting('company_phone', '');
        $data['title']           = 'Quotation / Estimate #' . $data['quotation']['quotation_number'];

        return view('quotations/print', $data);
    }
}
