<?php

namespace App\Controllers;

use App\Models\ProductionVendorModel;

class ProductionVendorController extends BaseController
{
    protected $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new ProductionVendorModel();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $data['vendors'] = $this->vendorModel->getVendorsWithFilters($filters);
        $data['title'] = 'Job Work Vendors';
        $data['filters'] = $filters;

        return view('production/vendors/index', $data);
    }

    public function view($id)
    {
        $data['vendor'] = $this->vendorModel->find($id);
        if (!$data['vendor']) {
            return redirect()->to('production/vendors')->with('error', 'Vendor not found.');
        }
        $data['title'] = 'Vendor Details';
        return view('production/vendors/view', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Job Work Vendor';
        return view('production/vendors/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->vendorModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['created_by'] = session('user_id');

        $this->vendorModel->save($data);
        return redirect()->to('production/vendors')->with('success', 'Vendor created successfully.');
    }

    public function edit($id)
    {
        $data['vendor'] = $this->vendorModel->find($id);
        if (!$data['vendor']) {
            return redirect()->to('production/vendors')->with('error', 'Vendor not found.');
        }
        $data['title'] = 'Edit Job Work Vendor';
        return view('production/vendors/form', $data);
    }

    public function update($id)
    {
        $rules = $this->vendorModel->getValidationRules();
        if (isset($rules['gst_number'])) {
            $rules['gst_number'] = str_replace('{id}', $id, $rules['gst_number']);
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['updated_by'] = session('user_id');

        $this->vendorModel->update($id, $data);
        return redirect()->to('production/vendors')->with('success', 'Vendor updated successfully.');
    }

    public function delete($id)
    {
        $this->vendorModel->delete($id);
        return redirect()->to('production/vendors')->with('success', 'Vendor deleted successfully.');
    }
}
