<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductCategoryModel;

class ProductCategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ProductCategoryModel();
    }

    public function index()
    {
        $data['categories'] = $this->categoryModel->getAllCategoriesWithNesting();
        $data['categoryTree'] = $this->categoryModel->getCategoryTree();
        $data['categoryPaths'] = $this->categoryModel->getCategoryPaths();
        return view('product_categories/index', $data);
    }

    public function store()
    {
        $rules = $this->categoryModel->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $parentId = $this->request->getPost('parent_id') ?: null;

        // Level Check: Max 4 levels (0 to 3 can be parents)
        if ($parentId) {
            $parent = $this->categoryModel->find($parentId);
            $level = 0;
            $currentId = $parentId;
            while ($currentId) {
                $level++;
                $p = $this->categoryModel->find($currentId);
                $currentId = $p['parent_id'];
            }
            if ($level >= 4) {
                return redirect()->back()->withInput()->with('error', 'Maximum category depth (4 levels) reached.');
            }
        }

        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'parent_id'     => $parentId,
            'description'   => $this->request->getPost('description'),
            'status'        => $this->request->getPost('status'),
        ];

        $this->categoryModel->insert($data);
        return redirect()->to('product_categories')->with('success', 'Category created successfully');
    }

    public function edit($id)
    {
        $data['category'] = $this->categoryModel->find($id);
        $data['categoryTree'] = $this->categoryModel->getCategoryTree();
        return view('product_categories/edit', $data);
    }

    public function update($id)
    {
        $rules = $this->categoryModel->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $parentId = $this->request->getPost('parent_id') ?: null;

        // Level Check: Max 4 levels
        if ($parentId) {
            $level = 0;
            $currentId = $parentId;
            while ($currentId) {
                $level++;
                $p = $this->categoryModel->find($currentId);
                $currentId = $p['parent_id'];
            }
            if ($level >= 4) {
                return redirect()->back()->withInput()->with('error', 'Maximum category depth (4 levels) reached.');
            }
        }

        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'parent_id'     => $parentId,
            'description'   => $this->request->getPost('description'),
            'status'        => $this->request->getPost('status'),
        ];

        $this->categoryModel->update($id, $data);
        return redirect()->to('product_categories')->with('success', 'Category updated successfully');
    }

    public function delete($id)
    {
        $this->categoryModel->delete($id);
        return redirect()->to('product_categories')->with('success', 'Category deleted successfully');
    }
}
