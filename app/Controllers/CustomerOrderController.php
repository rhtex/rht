<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\CustomerOrderModel;
use App\Models\ProductModel;

class CustomerOrderController extends BaseController
{
    protected CustomerOrderModel $orderModel;

    public function __construct()
    {
        $this->orderModel = new CustomerOrderModel();
    }

    public function index()
    {
        $search     = $this->request->getGet('search');
        $status     = $this->request->getGet('status');
        $customerId = (int) ($this->request->getGet('customer_id') ?? 0);

        $customers = (new CustomerModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $orders    = $this->orderModel->getWithCustomer($search, $status, $customerId ?: null);

        return view('customer_orders/index', [
            'orders'     => $orders,
            'customers'  => $customers,
            'statuses'   => CustomerOrderModel::STATUSES,
            'search'     => $search,
            'status'     => $status,
            'customerId' => $customerId,
        ]);
    }

    public function create()
    {
        return view('customer_orders/form', [
            'order'     => null,
            'customers' => (new CustomerModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'products'  => (new ProductModel())->where('status', 'active')->orderBy('product_name', 'ASC')->findAll(),
            'statuses'  => CustomerOrderModel::STATUSES,
        ]);
    }

    public function store()
    {
        $data = $this->collectPostData();
        $data['created_by'] = session('user_id');

        if (empty($data['customer_id']) || $data['design_no'] === '' || $data['product_name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Customer, Product Name, and Design No are required.');
        }

        if ($imgPath = $this->handleImageUpload()) {
            $data['image'] = $imgPath;
        }

        $this->orderModel->insert($data);
        return redirect()->to('customer_orders')->with('success', 'Customer order created successfully.');
    }

    public function edit($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('customer_orders')->with('error', 'Customer order not found.');
        }

        return view('customer_orders/form', [
            'order'     => $order,
            'customers' => (new CustomerModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'products'  => (new ProductModel())->where('status', 'active')->orderBy('product_name', 'ASC')->findAll(),
            'statuses'  => CustomerOrderModel::STATUSES,
        ]);
    }

    public function update($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('customer_orders')->with('error', 'Customer order not found.');
        }

        $data = $this->collectPostData();
        $data['updated_by'] = session('user_id');

        if (empty($data['customer_id']) || $data['design_no'] === '' || $data['product_name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Customer, Product Name, and Design No are required.');
        }

        if ($imgPath = $this->handleImageUpload()) {
            $data['image'] = $imgPath;
        }

        $this->orderModel->update($id, $data);
        return redirect()->to('customer_orders')->with('success', 'Customer order updated successfully.');
    }

    public function delete($id)
    {
        $order = $this->orderModel->find($id);
        if ($order) {
            if (!empty($order['image']) && file_exists(FCPATH . $order['image'])) {
                @unlink(FCPATH . $order['image']);
            }
            $this->orderModel->delete($id);
        }

        return redirect()->to('customer_orders')->with('success', 'Customer order deleted.');
    }

    public function byCustomer($customerId)
    {
        $orders = $this->orderModel->getOrdersForCustomer((int) $customerId);
        return $this->response->setJSON([
            'success' => true,
            'orders'  => $orders,
        ]);
    }

    private function collectPostData(): array
    {
        $productId   = (int) ($this->request->getPost('product_id') ?? 0);
        $productName = trim((string) ($this->request->getPost('product_name') ?? ''));
        if ($productId > 0 && $productName === '') {
            $prod = (new ProductModel())->find($productId);
            if ($prod) {
                $productName = $prod['product_name'] ?? '';
            }
        }

        return [
            'customer_id'  => (int) $this->request->getPost('customer_id'),
            'product_id'   => $productId > 0 ? $productId : null,
            'product_name' => $productName,
            'design_no'    => trim((string) $this->request->getPost('design_no')),
            'rate'         => (float) ($this->request->getPost('rate') ?? 0),
            'order_date'   => $this->request->getPost('order_date') ?: date('Y-m-d'),
            'status'       => $this->request->getPost('status') ?: 'Pending',
            'notes'        => trim((string) ($this->request->getPost('notes') ?? '')),
        ];
    }

    private function handleImageUpload(): ?string
    {
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dir = FCPATH . 'uploads/customer_orders/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $newName = 'co_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file->getExtension();
            $file->move($dir, $newName);
            return 'uploads/customer_orders/' . $newName;
        }
        return null;
    }
}
