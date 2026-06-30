<?php

namespace App\Controllers;

use App\Models\ProductionYarnModel;
use App\Models\ProductionYarnMasterModel;

class ProductionYarnController extends BaseController
{
    protected $yarnModel;
    protected $yarnMasterModel;

    public function __construct()
    {
        $this->yarnModel = new ProductionYarnModel();
        $this->yarnMasterModel = new ProductionYarnMasterModel();
    }

    public function index()
    {
        $filters = [
            'search'    => $this->request->getGet('search'),
            'yarn_type' => $this->request->getGet('yarn_type'),
            'status'    => $this->request->getGet('status'),
        ];

        $data['yarns'] = $this->yarnModel->getYarnsWithFilters($filters);
        $data['title'] = 'Yarn Stock Management';
        $data['filters'] = $filters;

        return view('production/yarns/index', $data);
    }

    public function view($id)
    {
        $data['yarn'] = $this->yarnModel->find($id);
        if (!$data['yarn']) {
            return redirect()->to('production/yarns')->with('error', 'Yarn record not found.');
        }
        $data['title'] = 'Yarn Details';
        return view('production/yarns/view', $data);
    }

    public function create()
    {
        $data['masterYarns'] = $this->yarnMasterModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Add New Yarn Stock';
        return view('production/yarns/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->yarnModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Use custom yarn name if "Other" is selected
        if (($data['name'] ?? '') === 'Other') {
            $data['name'] = $data['custom_name'] ?? '';
        }

        $data['created_by'] = session('user_id');

        $this->yarnModel->save($data);
        return redirect()->to('production/yarns')->with('success', 'Yarn stock record created successfully.');
    }

    public function edit($id)
    {
        $data['yarn'] = $this->yarnModel->find($id);
        if (!$data['yarn']) {
            return redirect()->to('production/yarns')->with('error', 'Yarn record not found.');
        }
        $data['masterYarns'] = $this->yarnMasterModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Edit Yarn Stock';
        return view('production/yarns/form', $data);
    }

    public function update($id)
    {
        $rules = $this->yarnModel->getValidationRules();
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Use custom yarn name if "Other" is selected
        if (($data['name'] ?? '') === 'Other') {
            $data['name'] = $data['custom_name'] ?? '';
        }

        $data['updated_by'] = session('user_id');

        $this->yarnModel->update($id, $data);
        return redirect()->to('production/yarns')->with('success', 'Yarn stock record updated successfully.');
    }

    public function delete($id)
    {
        $this->yarnModel->delete($id);
        return redirect()->to('production/yarns')->with('success', 'Yarn stock record deleted successfully.');
    }

    // ==========================================
    // YARN MASTER CRUD (Predefined Yarns)
    // ==========================================

    public function masterIndex()
    {
        $data['masterYarns'] = $this->yarnMasterModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Manage Predefined Yarns';
        return view('production/yarns/master_index', $data);
    }

    public function masterCreate()
    {
        $data['title'] = 'Add Predefined Yarn';
        return view('production/yarns/master_form', $data);
    }

    public function masterStore()
    {
        if (!$this->validate($this->yarnMasterModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->yarnMasterModel->save($this->request->getPost());
        return redirect()->to('production/yarns/master')->with('success', 'Predefined yarn added successfully.');
    }

    public function masterEdit($id)
    {
        $data['yarn'] = $this->yarnMasterModel->find($id);
        if (!$data['yarn']) {
            return redirect()->to('production/yarns/master')->with('error', 'Predefined yarn not found.');
        }
        $data['title'] = 'Edit Predefined Yarn';
        return view('production/yarns/master_form', $data);
    }

    public function masterUpdate($id)
    {
        if (!$this->validate($this->yarnMasterModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->yarnMasterModel->update($id, $this->request->getPost());
        return redirect()->to('production/yarns/master')->with('success', 'Predefined yarn updated successfully.');
    }

    public function masterDelete($id)
    {
        $this->yarnMasterModel->delete($id);
        return redirect()->to('production/yarns/master')->with('success', 'Predefined yarn deleted successfully.');
    }
}
