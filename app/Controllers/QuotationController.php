<?php

namespace App\Controllers;

use App\Models\QuotationModel;
use App\Models\QuotationItemModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\TransportModel;
use App\Services\ZohoBooksService;

class QuotationController extends BaseController
{
    protected $quotationModel;
    protected $itemModel;
    protected $customerModel;
    protected $productModel;
    protected $taxModel;
    protected $agentModel;
    protected $transportModel;
    protected $zohoService;

    public function __construct()
    {
        $this->quotationModel = new QuotationModel();
        $this->itemModel = new QuotationItemModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->taxModel = new \App\Models\TaxModel();
        $this->agentModel = new \App\Models\AgentModel();
        $this->transportModel = new TransportModel();
        $this->zohoService = new ZohoBooksService();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status' => $this->request->getGet('status'),
        ];

        $data['quotations'] = $this->quotationModel->getQuotationsWithCustomer($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['title'] = 'Quotations';

        return view('quotations/index', $data);
    }

    public function create()
    {
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['quotation_number'] = $this->quotationModel->generateQuotationNumber();
        $data['title'] = 'Create Quotation';
        $data['quotation'] = null;

        return view('quotations/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $quotationData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'quotation_number' => $this->request->getPost('quotation_number'),
            'quotation_date' => $this->request->getPost('quotation_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'transport_name' => $this->request->getPost('transport_name'),
            'waybill_number' => $this->request->getPost('waybill_number'),
            'notes' => $this->request->getPost('notes'),
            'terms' => $this->request->getPost('terms'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'Fixed',
            'shipping_charge' => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount' => $this->request->getPost('roundoff_amount') ?? 0,
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'created_by' => session('user_id'),
            'status' => 'Draft'
        ];

        // Manual Calculation for Totals
        $items = $this->request->getPost('items');
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['rate']);
        }

        $totalDiscount = ($quotationData['discount_type'] == 'Percentage') ? ($subtotal * $quotationData['discount_amount'] / 100) : $quotationData['discount_amount'];

        $taxAmount = 0;
        foreach ($items as $item) {
            $itemAmt = $item['quantity'] * $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
            $taxAmount += ($itemAmt - $itemDiscount) * ($item['tax_percentage'] / 100);
        }

        $quotationData['subtotal'] = $subtotal;
        $quotationData['tax_amount'] = $taxAmount;
        $quotationData['total_amount'] = ($subtotal - $totalDiscount) + $taxAmount + $quotationData['shipping_charge'] + $quotationData['roundoff_amount'];

        if (!$this->quotationModel->insert($quotationData)) {
            return redirect()->back()->withInput()->with('errors', $this->quotationModel->errors());
        }

        $quotationId = $this->quotationModel->getInsertID();

        foreach ($items as $item) {
            $this->itemModel->insert([
                'quotation_id' => $quotationId,
                'product_id' => $item['product_id'] ?: null,
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'tax_percentage' => $item['tax_percentage'],
                'amount' => $item['quantity'] * $item['rate']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create quotation.');
        }

        $this->pushToZoho($quotationId);

        return redirect()->to('quotations/view/' . $quotationId)->with('success', 'Quotation created successfully.');
    }

    public function view($id)
    {
        $data['quotation'] = $this->quotationModel->getQuotationById($id);
        if (!$data['quotation']) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        $data['title'] = 'Quotation #' . $data['quotation']['quotation_number'];
        return view('quotations/view', $data);
    }

    public function edit($id)
    {
        $data['quotation'] = $this->quotationModel->getQuotationById($id);
        if (!$data['quotation']) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
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
        $db = \Config\Database::connect();
        $db->transStart();

        $quotationData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'quotation_date' => $this->request->getPost('quotation_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'transport_name' => $this->request->getPost('transport_name'),
            'waybill_number' => $this->request->getPost('waybill_number'),
            'notes' => $this->request->getPost('notes'),
            'terms' => $this->request->getPost('terms'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'Fixed',
            'shipping_charge' => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount' => $this->request->getPost('roundoff_amount') ?? 0,
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'updated_by' => session('user_id'),
        ];

        $items = $this->request->getPost('items');
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['rate']);
        }

        $totalDiscount = ($quotationData['discount_type'] == 'Percentage') ? ($subtotal * $quotationData['discount_amount'] / 100) : $quotationData['discount_amount'];

        $taxAmount = 0;
        foreach ($items as $item) {
            $itemAmt = $item['quantity'] * $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
            $taxAmount += ($itemAmt - $itemDiscount) * ($item['tax_percentage'] / 100);
        }

        $quotationData['subtotal'] = $subtotal;
        $quotationData['tax_amount'] = $taxAmount;
        $quotationData['total_amount'] = ($subtotal - $totalDiscount) + $taxAmount + $quotationData['shipping_charge'] + $quotationData['roundoff_amount'];

        $this->quotationModel->update($id, $quotationData);

        $this->itemModel->where('quotation_id', $id)->delete();
        foreach ($items as $item) {
            $this->itemModel->insert([
                'quotation_id' => $id,
                'product_id' => $item['product_id'] ?: null,
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'tax_percentage' => $item['tax_percentage'],
                'amount' => $item['quantity'] * $item['rate']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update quotation.');
        }

        $this->pushToZoho($id);

        return redirect()->to('quotations/view/' . $id)->with('success', 'Quotation updated successfully.');
    }

    public function delete($id)
    {
        $quotation = $this->quotationModel->find($id);
        if (!$quotation) {
            return redirect()->to('quotations')->with('error', 'Quotation not found.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $this->itemModel->where('quotation_id', $id)->delete();
        $this->quotationModel->delete($id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('quotations')->with('error', 'Failed to delete quotation.');
        }

        if ($quotation['zoho_estimate_id']) {
            $this->zohoService->voidEstimate($quotation['zoho_estimate_id']);
        }

        return redirect()->to('quotations')->with('success', 'Quotation deleted successfully.');
    }

    private function pushToZoho($id)
    {
        $quotation = $this->quotationModel->getQuotationById($id);
        $customer = $this->customerModel->find($quotation['customer_id']);

        if (!$customer['zoho_contact_id'])
            return false;

        $zohoData = [
            'customer_id' => $customer['zoho_contact_id'],
            'estimate_number' => $quotation['quotation_number'],
            'date' => $quotation['quotation_date'],
            'expiry_date' => $quotation['expiry_date'],
            'reference_number' => $quotation['reference_number'],
            'discount' => $quotation['discount_amount'],
            'discount_type' => strtolower($quotation['discount_type']),
            'shipping_charge' => $quotation['shipping_charge'],
            'notes' => $quotation['notes'],
            'terms' => $quotation['terms'],
            'line_items' => []
        ];

        foreach ($quotation['items'] as $item) {
            $zohoData['line_items'][] = [
                'name' => $item['description'],
                'rate' => $item['rate'],
                'quantity' => $item['quantity'],
                'hsn_or_sac' => $item['hsn_code'],
                'tax_percentage' => $item['tax_percentage']
            ];
        }

        if ($quotation['zoho_estimate_id']) {
            $response = $this->zohoService->updateEstimate($quotation['zoho_estimate_id'], $zohoData);
        } else {
            $response = $this->zohoService->createEstimate($zohoData);
        }

        if ($response['success']) {
            $this->quotationModel->update($id, [
                'zoho_estimate_id' => $response['data']['estimate']['estimate_id'],
                'zoho_sync_status' => 'Synced',
                'zoho_sync_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        }

        $this->quotationModel->update($id, ['zoho_sync_status' => 'Failed']);
        return false;
    }

    public function print($id)
    {
        $data['quotation'] = $this->quotationModel->getQuotationById($id);
        if (!$data['quotation'])
            return 'Quotation not found';

        $data['company_name'] = get_setting('app_name', 'RasiDev');
        $data['company_address'] = get_setting('company_address', '');
        $data['company_gstin'] = get_setting('company_gstin', '');
        $data['title'] = 'Quotation ' . $data['quotation']['quotation_number'];

        return view('quotations/print', $data);
    }
}
