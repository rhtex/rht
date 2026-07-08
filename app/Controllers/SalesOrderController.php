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

    public function __construct()
    {
        $this->orderModel = new SalesOrderModel();
        $this->itemModel = new SalesOrderItemModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->taxModel = new \App\Models\TaxModel();
        $this->agentModel = new \App\Models\AgentModel();
        $this->transportModel = new TransportModel();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status' => $this->request->getGet('status'),
        ];

        $data['orders'] = $this->orderModel->getOrdersWithCustomer($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['title'] = 'Sales Orders';

        return view('sales_orders/index', $data);
    }

    public function create()
    {
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['order_number'] = $this->orderModel->generateOrderNumber();
        $data['title'] = 'Create Sales Order';
        $data['order'] = null;

        return view('sales_orders/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $orderData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'sales_order_number' => $this->request->getPost('sales_order_number'),
            'order_date' => $this->request->getPost('order_date'),
            'shipment_date' => $this->request->getPost('shipment_date'),
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

        $items = $this->request->getPost('items');
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


        return redirect()->to('sales_orders/view/' . $orderId)->with('success', 'Sales Order created successfully.');
    }

    public function view($id)
    {
        $data['order'] = $this->orderModel->getOrderById($id);
        if (!$data['order']) {
            return redirect()->to('sales_orders')->with('error', 'Order not found.');
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
        $db = \Config\Database::connect();
        $db->transStart();

        $orderData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'order_date' => $this->request->getPost('order_date'),
            'shipment_date' => $this->request->getPost('shipment_date'),
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


        return redirect()->to('sales_orders/view/' . $id)->with('success', 'Sales Order updated successfully.');
    }

    public function delete($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('sales_orders')->with('error', 'Order not found.');
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
