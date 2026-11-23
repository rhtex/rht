<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\CustomerOrderDesignModel;
use App\Models\CustomerOrderModel;
use App\Models\ProductModel;

class CustomerOrderController extends BaseController
{
    protected CustomerOrderModel $orderModel;
    protected CustomerOrderDesignModel $designModel;

    public function __construct()
    {
        $this->orderModel  = new CustomerOrderModel();
        $this->designModel = new CustomerOrderDesignModel();
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

    public function view($id)
    {
        $order = $this->orderModel->getOrderWithDesigns((int) $id);
        if (!$order) {
            return redirect()->to('customer_orders')->with('error', 'Customer order not found.');
        }

        return view('customer_orders/view', ['order' => $order]);
    }

    public function create()
    {
        return view('customer_orders/form', [
            'order'           => null,
            'designs'         => [],
            'nextOrderNumber' => $this->orderModel->generateOrderNumber(),
            'customers'       => (new CustomerModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'products'        => (new ProductModel())->where('status', 'active')->orderBy('product_name', 'ASC')->findAll(),
            'statuses'        => CustomerOrderModel::STATUSES,
        ]);
    }

    public function store()
    {
        $customerId  = (int) $this->request->getPost('customer_id');
        $orderDate   = $this->request->getPost('order_date') ?: date('Y-m-d');
        $orderNumber = trim((string) ($this->request->getPost('order_number') ?: $this->orderModel->generateOrderNumber()));
        $status      = in_array($this->request->getPost('status'), CustomerOrderModel::STATUSES, true) ? $this->request->getPost('status') : 'Active';
        $notes       = trim((string) ($this->request->getPost('notes') ?? ''));

        $designs = $this->collectDesignsFromPost();
        if ($customerId <= 0 || empty($designs)) {
            return redirect()->back()->withInput()->with('error', 'Customer and at least one design line item are required.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $first = $designs[0];
        $this->orderModel->insert([
            'order_number' => $orderNumber,
            'customer_id'  => $customerId,
            'order_date'   => $orderDate,
            'status'       => $status,
            'notes'        => $notes,
            'product_id'   => $first['product_id'],
            'product_name' => $first['product_name'],
            'design_no'    => (string) $first['design_no'],
            'rate'         => $first['rate'],
            'image'        => $first['image'],
            'created_by'   => session('user_id'),
        ]);
        $orderId = (int) $this->orderModel->getInsertID();

        foreach ($designs as $d) {
            $d['customer_order_id'] = $orderId;
            $this->designModel->insert($d);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save customer order.');
        }

        return redirect()->to('customer_orders')->with('success', 'Customer order created successfully.');
    }

    public function edit($id)
    {
        $order = $this->orderModel->getOrderWithDesigns((int) $id);
        if (!$order) {
            return redirect()->to('customer_orders')->with('error', 'Customer order not found.');
        }

        return view('customer_orders/form', [
            'order'           => $order,
            'designs'         => $order['designs'] ?? [],
            'nextOrderNumber' => $order['order_number'] ?: ('CO-' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT)),
            'customers'       => (new CustomerModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'products'        => (new ProductModel())->where('status', 'active')->orderBy('product_name', 'ASC')->findAll(),
            'statuses'        => CustomerOrderModel::STATUSES,
        ]);
    }

    public function update($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('customer_orders')->with('error', 'Customer order not found.');
        }

        $customerId  = (int) $this->request->getPost('customer_id');
        $orderDate   = $this->request->getPost('order_date') ?: date('Y-m-d');
        $orderNumber = trim((string) ($this->request->getPost('order_number') ?: $order['order_number']));
        $status      = in_array($this->request->getPost('status'), CustomerOrderModel::STATUSES, true) ? $this->request->getPost('status') : ($order['status'] ?: 'Active');
        $notes       = trim((string) ($this->request->getPost('notes') ?? ''));

        $designs = $this->collectDesignsFromPost();
        if ($customerId <= 0 || empty($designs)) {
            return redirect()->back()->withInput()->with('error', 'Customer and at least one design line item are required.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $first = $designs[0];
        $this->orderModel->update($id, [
            'order_number' => $orderNumber,
            'customer_id'  => $customerId,
            'order_date'   => $orderDate,
            'status'       => $status,
            'notes'        => $notes,
            'product_id'   => $first['product_id'],
            'product_name' => $first['product_name'],
            'design_no'    => (string) $first['design_no'],
            'rate'         => $first['rate'],
            'image'        => $first['image'],
            'updated_by'   => session('user_id'),
        ]);

        $this->designModel->where('customer_order_id', $id)->delete();
        foreach ($designs as $d) {
            $d['customer_order_id'] = (int) $id;
            $this->designModel->insert($d);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update customer order.');
        }

        return redirect()->to('customer_orders')->with('success', 'Customer order updated successfully.');
    }

    public function toggleStatus($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to('customer_orders')->with('error', 'Customer order not found.');
        }

        $newStatus = ($order['status'] === 'Closed') ? 'Active' : 'Closed';
        $this->orderModel->update($id, ['status' => $newStatus, 'updated_by' => session('user_id')]);

        return redirect()->back()->with('success', 'Order ' . ($order['order_number'] ?? '') . ' marked as ' . $newStatus . '.');
    }

    public function delete($id)
    {
        $order = $this->orderModel->find($id);
        if ($order) {
            $designs = $this->designModel->getByOrderId((int) $id);
            foreach ($designs as $d) {
                if (!empty($d['image']) && file_exists(FCPATH . $d['image'])) {
                    @unlink(FCPATH . $d['image']);
                }
            }
            $this->designModel->where('customer_order_id', $id)->delete();
            $this->orderModel->delete($id);
        }

        return redirect()->to('customer_orders')->with('success', 'Customer order deleted.');
    }

    public function byCustomer($customerId)
    {
        $rows = $this->orderModel->getActiveDesignsForCustomer((int) $customerId);
        // Format display label cleanly for Invoice dropdown
        foreach ($rows as &$r) {
            $r['label'] = '#' . $r['design_no'] . ' (' . ($r['order_number'] ?? 'CO') . ') - ' . $r['product_name'];
        }
        return $this->response->setJSON([
            'success' => true,
            'orders'  => $rows,
        ]);
    }

    private function collectDesignsFromPost(): array
    {
        $rawDesigns = $this->request->getPost('designs') ?? [];
        $files      = $this->request->getFiles();
        $designFiles = $files['designs'] ?? [];

        $productModel = new ProductModel();
        $result = [];
        $seq    = 1;

        foreach ($rawDesigns as $idx => $row) {
            $productId   = (int) ($row['product_id'] ?? 0);
            $productName = trim((string) ($row['product_name'] ?? ''));
            if ($productId > 0 && $productName === '') {
                $prod = $productModel->find($productId);
                $productName = $prod['product_name'] ?? '';
            }
            if ($productId <= 0 && $productName === '') {
                continue;
            }

            $imagePath = trim((string) ($row['existing_image'] ?? '')) ?: null;
            $file = $designFiles[$idx]['image_file'] ?? null;
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $dir = FCPATH . 'uploads/customer_orders/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $newName = 'co_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file->getExtension();
                $file->move($dir, $newName);
                $imagePath = 'uploads/customer_orders/' . $newName;
            }

            $result[] = [
                'design_no'    => $seq++,
                'product_id'   => $productId > 0 ? $productId : null,
                'product_name' => $productName,
                'rate'         => (float) ($row['rate'] ?? 0),
                'image'        => $imagePath,
            ];
        }

        return $result;
    }
}
