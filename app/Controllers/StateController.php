<?php

namespace App\Controllers;

use App\Models\StateModel;
use App\Models\CountryModel;

class StateController extends BaseController
{
    protected $stateModel;
    protected $countryModel;

    public function __construct()
    {
        $this->stateModel = new StateModel();
        $this->countryModel = new CountryModel();
    }

    public function index()
    {
        $data['states'] = $this->stateModel
            ->select('states.*, countries.name as country_name')
            ->join('countries', 'countries.id = states.country_id')
            ->findAll();
        $data['title'] = 'States';
        return view('states/index', $data);
    }

    public function create()
    {
        $data['countries'] = $this->countryModel->findAll();
        $data['title'] = 'Add New State';
        return view('states/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->stateModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->stateModel->save($this->request->getPost());
        return redirect()->to('states')->with('success', 'State created successfully.');
    }

    public function edit($id)
    {
        $data['state'] = $this->stateModel->find($id);
        if (!$data['state']) {
            return redirect()->to('states')->with('error', 'State not found.');
        }

        $data['countries'] = $this->countryModel->findAll();
        $data['title'] = 'Edit State';
        return view('states/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate($this->stateModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->stateModel->update($id, $this->request->getPost());
        return redirect()->to('states')->with('success', 'State updated successfully.');
    }

    public function delete($id)
    {
        $this->stateModel->delete($id);
        return redirect()->to('states')->with('success', 'State deleted successfully.');
    }
}
