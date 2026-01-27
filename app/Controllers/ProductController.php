<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ProductCategoryModel;
use App\Models\ProductItemModel;
use App\Models\ProductImageModel;
use App\Models\VendorModel;

class ProductController extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $itemModel;
    protected $imageModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->productModel  = new ProductModel();
        $this->categoryModel = new ProductCategoryModel();
        $this->itemModel     = new ProductItemModel();
        $this->imageModel    = new ProductImageModel();
        $this->vendorModel   = new VendorModel();
    }

    public function index()
    {
        $filters = [
            'category_id' => $this->request->getGet('category_id'),
            'search'      => $this->request->getGet('search'),
            'status'      => $this->request->getGet('status'),
        ];

        $data['products']     = $this->productModel->getFilteredProducts($filters);
        $data['categories']   = $this->categoryModel->getCategoryTree();
        $data['category_paths'] = $this->categoryModel->getCategoryPaths();
        $data['total_count']  = count($data['products']);
        $data['applied_filters'] = $filters;

        return view('products/index', $data);
    }

    public function create()
    {
        $data['categories'] = $this->categoryModel->getCategoryTree();
        $taxModel = new \App\Models\TaxModel();
        $data['taxes'] = $taxModel->where('status', 'Active')->findAll();
        return view('products/create', $data);
    }

    public function store()
    {
        $rules = $this->productModel->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'product_name'  => $this->request->getPost('product_name'),
            'barcode'       => $this->request->getPost('barcode') ?: null,
            'category_id'   => $this->request->getPost('category_id'),
            'hsn_code'      => $this->request->getPost('hsn_code'),
            'description'   => $this->request->getPost('description'),
            'unit'          => $this->request->getPost('unit'),
            'selling_price' => $this->request->getPost('selling_price'),
            'tax_id'        => $this->request->getPost('tax_id') ?: null,
            'status'        => $this->request->getPost('status'),
        ];

        $productId = $this->productModel->insert($data);

        if (!$productId) {
            return redirect()->back()->withInput()->with('error', 'Failed to save product information.');
        }

        // Handle Multiple Common Images
        $files = $this->request->getFileMultiple('product_images');
        if ($files) {
            foreach ($files as $index => $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/products', $newName);
                    $this->imageModel->insert([
                        'product_id' => $productId,
                        'image_path' => 'uploads/products/' . $newName,
                        'is_primary' => ($index === 0) ? 1 : 0
                    ]);
                }
            }
        }

        return redirect()->to('products')->with('success', 'Product created successfully');
    }

    public function view($id)
    {
        $data['product']    = $this->productModel->select('products.*, product_categories.category_name, taxes.percentage as tax_percentage')
                                             ->join('product_categories', 'product_categories.id = products.category_id', 'left')
                                             ->join('taxes', 'taxes.id = products.tax_id', 'left')
                                             ->where('products.id', $id)
                                             ->first();
        
        if (!$data['product']) {
            return redirect()->to('products')->with('error', 'Product not found.');
        }

        $data['images']     = $this->imageModel->getProductImages($id);

        // Filter Logic
        $filters = [
            'status'      => $this->request->getGet('status'),
            'is_approved' => $this->request->getGet('approval_status'),
        ];
        $data['filters']    = $filters;
        $data['items']      = $this->itemModel->getItemsByProduct($id, $filters);
        $data['vendors']    = $this->vendorModel->findAll();

        // Fetch last item to pre-fill form
        $lastItem = $this->itemModel->where('product_id', $id)->orderBy('id', 'DESC')->first() ?: [];
        
        $nextBarcode = '';
        if (!empty($lastItem)) {
            $barcode = $lastItem['barcode'];
            if (preg_match('/^(.*?)(\d+)$/', $barcode, $matches)) {
                $nextBarcode = $matches[1] . str_pad((int)$matches[2] + 1, strlen($matches[2]), '0', STR_PAD_LEFT);
            } else {
                $nextBarcode = $barcode . '1';
            }
        }
        $data['last_item'] = $lastItem;
        $data['next_barcode'] = $nextBarcode;

        return view('products/view', $data);
    }

    // Manage Quantity (Items)
    public function addItem($productId)
    {
        $product = $this->productModel->find($productId);
        $rules = [
            'barcode'        => "required|is_unique[product_items.barcode]",
            'purchase_price' => 'required|decimal',
            'selling_price'  => 'permit_empty|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }


        $receivedImage = null;
        $file = $this->request->getFile('received_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/items', $newName);
            $receivedImage = 'uploads/items/' . $newName;
        }

        $data = [
            'product_id'        => $productId,
            'barcode'           => $this->request->getPost('barcode'),
            'purchase_price'    => $this->request->getPost('purchase_price'),
            'selling_price'     => $this->request->getPost('selling_price') ?: $product['selling_price'],
            'vendor_id'         => $this->request->getPost('vendor_id') ?: null,
            'vendor_invoice_no' => $this->request->getPost('vendor_invoice_no'),
            'remarks'           => $this->request->getPost('remarks'),
            'status'            => 'received',
            'is_approved'       => 'Pending',
            'received_image'    => $receivedImage,
            'created_by'        => session()->get('user_id'),
        ];

        $this->itemModel->insert($data);
        $this->productModel->updateStock($productId);

        return redirect()->back()->with('success', 'Item added to inventory');
    }

    public function edit($id)
    {
        $data['product']    = $this->productModel->find($id);
        $data['categories'] = $this->categoryModel->getCategoryTree();
        $taxModel = new \App\Models\TaxModel();
        $data['taxes'] = $taxModel->where('status', 'Active')->findAll();
        return view('products/edit', $data);
    }

    public function update($id)
    {
        $rules = $this->productModel->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'product_name'  => $this->request->getPost('product_name'),
            'barcode'       => $this->request->getPost('barcode') ?: null,
            'category_id'   => $this->request->getPost('category_id'),
            'hsn_code'      => $this->request->getPost('hsn_code'),
            'description'   => $this->request->getPost('description'),
            'unit'          => $this->request->getPost('unit'),
            'selling_price' => $this->request->getPost('selling_price'),
            'tax_id'        => $this->request->getPost('tax_id') ?: null,
            'status'        => $this->request->getPost('status'),
        ];

        $this->productModel->update($id, $data);
        return redirect()->to('products')->with('success', 'Product updated successfully');
    }

    public function delete($id)
    {
        // Should handle deleting items and images too, but maybe just logical delete or restricted
        $this->productModel->delete($id);
        return redirect()->to('products')->with('success', 'Product deleted');
    }

    public function approveItem($itemId)
    {
        $item = $this->itemModel->find($itemId);
        if (!$item) {
            return redirect()->back()->with('error', 'Item not found');
        }

        $verifiedImage = null;
        $file = $this->request->getFile('verified_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/items', $newName);
            $verifiedImage = 'uploads/items/' . $newName;
        } else {
            return redirect()->back()->with('error', 'Verification image is required for approval.');
        }

        $data = [
            'verified_image' => $verifiedImage,
            'is_approved'    => 'Approved',
            'status'         => 'available',
            'approved_by'    => session()->get('user_id'),
            'approved_at'    => date('Y-m-d H:i:s')
        ];

        $this->itemModel->update($itemId, $data);
        $this->productModel->updateStock($item['product_id']); // Update stock only after approval
        return redirect()->back()->with('success', 'Item approved and added to inventory');
    }

    public function rejectItem($itemId)
    {
        $item = $this->itemModel->find($itemId);
        if (!$item) {
            return redirect()->back()->with('error', 'Item not found');
        }

        $rejectionImage = null;
        $file = $this->request->getFile('rejection_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/items', $newName);
            $rejectionImage = 'uploads/items/' . $newName;
        }

        $data = [
            'rejection_image'  => $rejectionImage,
            'rejection_reason' => $this->request->getPost('rejection_reason'),
            'is_approved'      => 'Rejected',
            'status'           => 'rejected',
            'approved_by'      => session()->get('user_id'), // Track who rejected it
            'approved_at'      => date('Y-m-d H:i:s')
        ];

        $this->itemModel->update($itemId, $data);
        return redirect()->back()->with('success', 'Item rejected');
    }

    public function markDamaged($itemId)
    {
        $item = $this->itemModel->find($itemId);
        if (!$item) {
            return redirect()->back()->with('error', 'Item not found');
        }

        $damageImage = null;
        $file = $this->request->getFile('damage_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/items', $newName);
            $damageImage = 'uploads/items/' . $newName;
        }

        $data = [
            'rejection_image'  => $damageImage, // Reuse rejection_image for damage evidence
            'rejection_reason' => $this->request->getPost('damage_reason'),
            'damage_price'     => $this->request->getPost('damage_price'),
            'status'           => 'damaged',
            // Keep is_approved as Approved since it was approved previously
        ];

        $this->itemModel->update($itemId, $data);
        $this->productModel->updateStock($item['product_id']); // Update stock as damaged items might not count as available
        return redirect()->back()->with('success', 'Item marked as damaged');
    }

    public function approvals()
    {
        $filters = [
            'is_approved' => $this->request->getGet('is_approved') ?? 'Pending',
            'barcode'     => $this->request->getGet('barcode')
        ];

        $data = [
            'title'   => 'Inventory Approvals',
            'items'   => $this->itemModel->getAllItems($filters),
            'filters' => $filters
        ];

        return view('products/approvals', $data);
    }

    public function getByBarcode($barcode)
    {
        // 1. Try to find in main products table
        $product = $this->productModel->select('products.*, taxes.percentage as tax_percentage')
                                      ->join('taxes', 'taxes.id = products.tax_id', 'left')
                                      ->where('barcode', $barcode)
                                      ->first();

        if (!$product) {
            // 2. Try to find in product items table
            $item = $this->itemModel->select('product_items.*, products.product_name, products.hsn_code, taxes.percentage as tax_percentage')
                                    ->join('products', 'products.id = product_items.product_id', 'left')
                                    ->join('taxes', 'taxes.id = products.tax_id', 'left')
                                    ->where('product_items.barcode', $barcode)
                                    ->where('product_items.status', 'available')
                                    ->first();
            
            if ($item) {
                // Map item to product structure
                $product = [
                    'id'             => $item['product_id'],
                    'product_name'   => $item['product_name'],
                    'hsn_code'       => $item['hsn_code'],
                    'selling_price'  => $item['selling_price'],
                    'tax_percentage' => $item['tax_percentage']
                ];
            }
        }

        if ($product) {
            return $this->response->setJSON([
                'status'  => 'success',
                'product' => $product
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Product not found'
        ]);
    }
}
