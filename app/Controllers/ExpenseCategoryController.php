<?php

namespace App\Controllers;

use App\Models\ExpenseCategoryModel;

class ExpenseCategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ExpenseCategoryModel();
    }

    public function index()
    {
        $data['categories'] = $this->categoryModel->findAll();
        $data['title'] = 'Expense Categories';
        return view('expense_categories/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Expense Category';
        return view('expense_categories/form', $data);
    }

    public function store()
    {
        if (!$this->categoryModel->insert($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->categoryModel->errors());
        }

        return redirect()->to('expense_categories')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $data['category'] = $this->categoryModel->find($id);
        if (!$data['category']) {
            return redirect()->to('expense_categories')->with('error', 'Category not found.');
        }

        $data['title'] = 'Edit Expense Category';
        return view('expense_categories/form', $data);
    }

    public function update($id)
    {
        if (!$this->categoryModel->update($id, $this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->categoryModel->errors());
        }

        return redirect()->to('expense_categories')->with('success', 'Category updated successfully.');
    }

    public function delete($id)
    {
        // Check if category is used in expenses
        $db = \Config\Database::connect();
        if ($db->table('expenses')->where('category_id', $id)->countAllResults() > 0) {
            return redirect()->to('expense_categories')->with('error', 'Cannot delete category as it is currently being used in expenses.');
        }

        $this->categoryModel->delete($id);
        return redirect()->to('expense_categories')->with('success', 'Category deleted successfully.');
    }
}
