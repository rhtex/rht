<?php

namespace App\Controllers;

use App\Models\ProductionAgreementModel;

class ProductionAgreementController extends BaseController
{
    protected $agreementModel;

    public function __construct()
    {
        $this->agreementModel = new ProductionAgreementModel();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $data['agreements'] = $this->agreementModel->getAgreementsWithFilters($filters);
        $data['title'] = 'Production Agreements';
        $data['filters'] = $filters;

        return view('production/agreements/index', $data);
    }

    public function view($id)
    {
        $data['agreement'] = $this->agreementModel->find($id);
        if (!$data['agreement']) {
            return redirect()->to('production/agreements')->with('error', 'Agreement not found.');
        }
        $data['title'] = 'Agreement Details';
        return view('production/agreements/view', $data);
    }

    public function create()
    {
        $weaverModel = new \App\Models\WeaverModel();
        $vendorModel = new \App\Models\ProductionVendorModel();
        
        $data['weavers'] = $weaverModel->orderBy('name', 'ASC')->findAll();
        $data['vendors'] = $vendorModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Add New Agreement';
        
        return view('production/agreements/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->agreementModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Use custom party name if "Other" is selected
        if (($data['party_name'] ?? '') === 'Other') {
            $data['party_name'] = $data['custom_party_name'] ?? '';
        }

        // Handle agreement file upload
        $file = $this->request->getFile('agreement_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/agreements', $newName);
            $data['agreement_file'] = 'uploads/agreements/' . $newName;
        }

        $data['created_by'] = session('user_id');

        $this->agreementModel->save($data);
        return redirect()->to('production/agreements')->with('success', 'Agreement created successfully.');
    }

    public function edit($id)
    {
        $data['agreement'] = $this->agreementModel->find($id);
        if (!$data['agreement']) {
            return redirect()->to('production/agreements')->with('error', 'Agreement not found.');
        }
        
        $weaverModel = new \App\Models\WeaverModel();
        $vendorModel = new \App\Models\ProductionVendorModel();
        
        $data['weavers'] = $weaverModel->orderBy('name', 'ASC')->findAll();
        $data['vendors'] = $vendorModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Edit Agreement';
        
        return view('production/agreements/form', $data);
    }

    public function update($id)
    {
        $rules = $this->agreementModel->getValidationRules();
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Use custom party name if "Other" is selected
        if (($data['party_name'] ?? '') === 'Other') {
            $data['party_name'] = $data['custom_party_name'] ?? '';
        }

        // Handle agreement file upload
        $file = $this->request->getFile('agreement_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old file if exists
            $agreement = $this->agreementModel->find($id);
            if (!empty($agreement['agreement_file']) && file_exists(FCPATH . $agreement['agreement_file'])) {
                @unlink(FCPATH . $agreement['agreement_file']);
            }
            
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/agreements', $newName);
            $data['agreement_file'] = 'uploads/agreements/' . $newName;
        }

        $data['updated_by'] = session('user_id');

        $this->agreementModel->update($id, $data);
        return redirect()->to('production/agreements')->with('success', 'Agreement updated successfully.');
    }

    public function delete($id)
    {
        // Delete file if exists
        $agreement = $this->agreementModel->find($id);
        if (!empty($agreement['agreement_file']) && file_exists(FCPATH . $agreement['agreement_file'])) {
            @unlink(FCPATH . $agreement['agreement_file']);
        }

        $this->agreementModel->delete($id);
        return redirect()->to('production/agreements')->with('success', 'Agreement deleted successfully.');
    }
}
