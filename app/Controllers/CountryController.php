<?php

namespace App\Controllers;

use App\Models\CountryModel;

class CountryController extends BaseController
{
    protected $countryModel;

    public function __construct()
    {
        $this->countryModel = new CountryModel();
    }

    public function index()
    {
        $data['countries'] = $this->countryModel->findAll();
        $data['title'] = 'Countries';
        return view('countries/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Country';
        return view('countries/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->countryModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->countryModel->save($this->request->getPost());
        return redirect()->to('countries')->with('success', 'Country created successfully.');
    }

    public function edit($id)
    {
        $data['country'] = $this->countryModel->find($id);
        if (!$data['country']) {
            return redirect()->to('countries')->with('error', 'Country not found.');
        }

        $data['title'] = 'Edit Country';
        return view('countries/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate($this->countryModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->countryModel->update($id, $this->request->getPost());
        return redirect()->to('countries')->with('success', 'Country updated successfully.');
    }

    public function delete($id)
    {
        $this->countryModel->delete($id);
        return redirect()->to('countries')->with('success', 'Country deleted successfully.');
    }
}
