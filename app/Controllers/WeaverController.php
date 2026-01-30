<?php

namespace App\Controllers;

use App\Models\WeaverModel;

class WeaverController extends BaseController
{
    protected $weaverModel;

    public function __construct()
    {
        $this->weaverModel = new WeaverModel();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $data['weavers'] = $this->weaverModel->getWeaversWithFilters($filters);
        $data['title'] = 'Weavers';
        $data['filters'] = $filters;

        return view('production/weavers/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Weaver';
        return view('production/weavers/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->weaverModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['created_by'] = session('user_id');

        $this->weaverModel->save($data);
        return redirect()->to('production/weavers')->with('success', 'Weaver created successfully.');
    }

    public function edit($id)
    {
        $data['weaver'] = $this->weaverModel->find($id);
        if (!$data['weaver']) {
            return redirect()->to('production/weavers')->with('error', 'Weaver not found.');
        }
        $data['title'] = 'Edit Weaver';
        return view('production/weavers/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate($this->weaverModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['updated_by'] = session('user_id');

        $this->weaverModel->update($id, $data);
        return redirect()->to('production/weavers')->with('success', 'Weaver updated successfully.');
    }

    public function delete($id)
    {
        $this->weaverModel->delete($id);
        return redirect()->to('production/weavers')->with('success', 'Weaver deleted successfully.');
    }
}
