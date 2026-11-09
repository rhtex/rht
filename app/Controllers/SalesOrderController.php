<?php

namespace App\Controllers;

use App\Models\SalesOrderModel;
use App\Models\SalesOrderItemModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\TransportModel;

class SalesOrderController extends BaseController
{
    protected $orderModel;
    protected $itemModel;
    protected $customerModel;
    protected $productModel;
    protected $taxModel;
    protected $agentModel;
    protected $transportModel;
    protected $quotationModel;

    public function __construct()
    {
        $this->orderModel = new SalesOrderModel();
        $this->itemModel = new SalesOrderItemModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->taxModel = new \App\Models\TaxModel();
        $this->agentModel = new \App\Models\AgentModel();
        $this->transportModel = new TransportModel();
        $this->quotationModel = new \App\Models\QuotationModel();
    }

    public function index()
    {
        $filters = [
            'sales_order_number' => $this->request->getGet('sales_order_number'),
            'customer_id'        => $this->request->getGet('customer_id'),
            'status'             => $this->request->getGet('status'),
            'date_from'          => $this->request->getGet('date_from'),
            'date_to'            => $this->request->getGet('date_to'),
        ];

        $data['orders'] = $this->orderModel->getOrdersWithCustomer($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['filters'] = $filters;
        $data['title'] = 'Sales Orders';

        $stats = [
            'total'           => count($data['orders']),
            'total_amount'    => 0.0,
            'confirmed_count' => 0,
            'converted_count' => 0,
            'draft_count'     => 0,
        ];
        foreach ($data['orders'] as $ord) {
            $stats['total_amount'] += (float) ($ord['total_amount'] ?? 0);
            $st = $ord['status'] ?? '';
            if ($st === 'Confirmed') {
                $stats['confirmed_count']++;
            } elseif ($st === 'Draft') {
                $stats['draft_count']++;
            }
            if (!empty($ord['converted_to_invoice_id'])) {
                $stats['converted_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('sales_orders/index', $data);
    }

    public function create()
    {
        $quotationId = $this->request->getGet('quotation_id');
        $quotation = null;
        if ($quotationId) {
            $quotation = $this->quotationModel->getQuotationById($quotationId);
        }

        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['order_number'] = $this->orderModel->generateOrderNumber();
        $data['title'] = 'Create Sales Order';
        $data['order'] = null;
        $data['quotation'] = $quotation;

        return view('sales_orders/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $orderData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'quotation_id' => $this->request->getPost('quotation_id') ?: null,
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'agent_commission_percent' => $this->request->getPost('agent_commission_percent') ?? 0,
            'sales_order_number' => $this->request->getPost('sales_order_number'),
            'order_date' => $this->request->getPost('order_date'),
            'shipment_date' => $this->request->getPost('shipment_date') ?: null,
            'reference_number' => $this->request->getPost('reference_number'),
            'selection_type' => $this->request->getPost('selection_type') ?: null,
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

        $rawItems = $this->request->getPost('items') ?? [];
        $items = [];
        foreach ($rawItems as $item) {
            $hasProduct = !empty($item['product_id']);
            $hasDesc = !empty(trim($item['description'] ?? ''));
            $qty = floatval($item['quantity'] ?? 0);
            if (($hasProduct || $hasDesc) && $qty > 0) {
                $items[] = $item;
            }
        }

        if (empty($items)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Please add at least one line item with quantity.');
        }
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['rate']);
        }

        $totalDiscount = ($orderData['discount_type'] == 'Percentage') ? ($subtotal * $orderData['discount_amount'] / 100) : $orderData['discount_amount'];

        $taxAmount = 0;
        foreach ($items as $item) {
            $itemAmt = $item['quantity'] * $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
            $taxAmount += ($itemAmt - $itemDiscount) * ($item['tax_percentage'] / 100);
        }

        $orderData['subtotal'] = $subtotal;
        $orderData['tax_amount'] = $taxAmount;
        $orderData['total_amount'] = ($subtotal - $totalDiscount) + $taxAmount + $orderData['shipping_charge'] + $orderData['roundoff_amount'];

        if (!$this->orderModel->insert($orderData)) {
            return redirect()->back()->withInput()->with('errors', $this->orderModel->errors());
        }

        $orderId = $this->orderModel->getInsertID();

        foreach ($items as $item) {
            $this->itemModel->insert([
                'sales_order_id' => $orderId,
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
            return redirect()->back()->withInput()->with('error', 'Failed to create sales order.');
        }

        $quotationId = $this->request->getPost('quotation_id');
        if (!empty($quotationId)) {
            $this->quotationModel->markConvertedToSo((int) $quotationId, $orderId);
            if (function_exists('zoho_auto_sync')) {
                zoho_auto_sync('quotations', (int) $quotationId, 'UPDATE');
            }
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('sales_orders', (int) $orderId, 'CREATE');
        }

        return redirect()->to('sales_orders/view/' . $orderId)->with('success', 'Sales Order created successfully.');
    }

    public function view($id)
    {
        $data['order'] = $this->orderModel->getOrderById($id);
        if (!$data['order']) {
            return redirect()->to('sales_orders')->with('error', 'Order not found.');
        }

        if ($data['order']['agent_id']) {
            $data['agent'] = $this->agentModel->find($data['order']['agent_id']);
        }

        $data['title'] = 'Sales Order #' . $data['order']['sales_order_number'];
        return view('sales_orders/view', $data);
    }

    public function edit($id)
    {
        $data['order'] = $this->orderModel->getOrderById($id);
        if (!$data['order']) {
            return redirect()->to('sales_orders')->with('error', 'Order not found.');
        }

        if (!empty($data['order']['converted_to_invoice_id']) || $data['order']['status'] == 'Closed') {
            return redirect()->to('sales_orders/view/' . $id)->with('error', 'Cannot edit a sales order that has already been converted to an invoice.');
        }

        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['title'] = 'Edit Sales Order #' . $data['order']['sales_order_number'];

        return view('sales_orders/form', $data);
    }

    public function update($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('sales_orders')->with('error', 'Order not found.');
        }
        if (!empty($order['converted_to_invoice_id']) || $order['status'] == 'Closed') {
            return redirect()->to('sales_orders/view/' . $id)->with('error', 'Cannot edit a sales order that has already been converted to an invoice.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $orderData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'quotation_id' => $this->request->getPost('quotation_id') ?: null,
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'agent_commission_percent' => $this->request->getPost('agent_commission_percent') ?? 0,
            'order_date' => $this->request->getPost('order_date'),
            'shipment_date' => $this->request->getPost('shipment_date') ?: null,
            'reference_number' => $this->request->getPost('reference_number'),
            'selection_type' => $this->request->getPost('selection_type') ?: null,
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

        $rawItems = $this->request->getPost('items') ?? [];
        $items = [];
        foreach ($rawItems as $item) {
            $hasProduct = !empty($item['product_id']);
            $hasDesc = !empty(trim($item['description'] ?? ''));
            $qty = floatval($item['quantity'] ?? 0);
            if (($hasProduct || $hasDesc) && $qty > 0) {
                $items[] = $item;
            }
        }

        if (empty($items)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Please add at least one line item with quantity.');
        }
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['rate']);
        }

        $totalDiscount = ($orderData['discount_type'] == 'Percentage') ? ($subtotal * $orderData['discount_amount'] / 100) : $orderData['discount_amount'];

        $taxAmount = 0;
        foreach ($items as $item) {
            $itemAmt = $item['quantity'] * $item['rate'];
            $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
            $taxAmount += ($itemAmt - $itemDiscount) * ($item['tax_percentage'] / 100);
        }

        $orderData['subtotal'] = $subtotal;
        $orderData['tax_amount'] = $taxAmount;
        $orderData['total_amount'] = ($subtotal - $totalDiscount) + $taxAmount + $orderData['shipping_charge'] + $orderData['roundoff_amount'];

        $this->orderModel->update($id, $orderData);

        $this->itemModel->where('sales_order_id', $id)->delete();
        foreach ($items as $item) {
            $this->itemModel->insert([
                'sales_order_id' => $id,
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
            return redirect()->back()->withInput()->with('error', 'Failed to update sales order.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('sales_orders', (int) $id, 'UPDATE');
        }

        return redirect()->to('sales_orders/view/' . $id)->with('success', 'Sales Order updated successfully.');
    }

    public function convertToInvoice($id)
    {
        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            return redirect()->to('sales_orders')->with('error', 'Sales Order not found.');
        }

        if (!empty($order['converted_to_invoice_id']) || $order['status'] == 'Closed') {
            if (!empty($order['converted_to_invoice_id'])) {
                return redirect()->to('invoices/view/' . $order['converted_to_invoice_id'])
                    ->with('info', 'This Sales Order has already been converted to an invoice.');
            }
            return redirect()->to('sales_orders/view/' . $id)
                ->with('error', 'This Sales Order has already been closed/converted and cannot be converted again.');
        }

        // Redirect to invoice creation page with SO id
        return redirect()->to('invoices/create?so_id=' . $id);
    }

    public function delete($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('sales_orders')->with('error', 'Order not found.');
        }

        if (!empty($order['converted_to_invoice_id']) || $order['status'] == 'Closed') {
            return redirect()->to('sales_orders/view/' . $id)->with('error', 'Cannot delete a sales order that has already been converted to an invoice.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('sales_orders', (int) $id, 'DELETE');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $this->itemModel->where('sales_order_id', $id)->delete();
        $this->orderModel->delete($id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('sales_orders')->with('error', 'Failed to delete order.');
        }


        return redirect()->to('sales_orders')->with('success', 'Sales Order deleted successfully.');
    }


    public function print($id)
    {
        $data['order'] = $this->orderModel->getOrderById($id);
        if (!$data['order'])
            return 'Order not found';

        $data['company_name'] = get_setting('app_name', 'RasiDev');
        $data['company_address'] = get_setting('company_address', '');
        $data['company_gstin'] = get_setting('company_gstin', '');
        $data['title'] = 'Sales Order ' . $data['order']['sales_order_number'];

        return view('sales_orders/print', $data);
    }
}
