<?php

namespace App\Controllers;

use App\Models\TaxModel;

class TaxController extends BaseController
{
    protected $taxModel;

    public function __construct()
    {
        $this->taxModel = new TaxModel();
    }

    public function index()
    {
        $data['taxes'] = $this->taxModel->findAll();
        $data['title'] = 'Taxes';
        return view('settings/taxes/index', $data);
    }

    public function new()
    {
        $data['title'] = 'New Tax';
        return view('settings/taxes/form', $data);
    }

    public function create()
    {
        $data = [
            'name'       => $this->request->getPost('name'),
            'percentage' => $this->request->getPost('percentage'),
            'status'     => $this->request->getPost('status'),
        ];

        if (!$this->taxModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->taxModel->errors());
        }

        return redirect()->to('settings/taxes')->with('success', 'Tax created successfully.');
    }

    public function edit($id)
    {
        $tax = $this->taxModel->find($id);
        if (!$tax) {
            return redirect()->to('settings/taxes')->with('error', 'Tax not found.');
        }

        $data['tax'] = $tax;
        $data['title'] = 'Edit Tax';
        return view('settings/taxes/form', $data);
    }

    public function update($id)
    {
        $data = [
            'id'         => $id,
            'name'       => $this->request->getPost('name'),
            'percentage' => $this->request->getPost('percentage'),
            'status'     => $this->request->getPost('status'),
        ];

        if (!$this->taxModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->taxModel->errors());
        }

        return redirect()->to('settings/taxes')->with('success', 'Tax updated successfully.');
    }

    public function delete($id)
    {
        if ($this->taxModel->delete($id)) {
            return redirect()->to('settings/taxes')->with('success', 'Tax deleted successfully.');
        }
        return redirect()->to('settings/taxes')->with('error', 'Failed to delete tax.');
    }
}
