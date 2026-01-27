<?php

namespace App\Controllers;

use App\Models\TransportModel;
use App\Models\CountryModel;
use App\Models\StateModel;

class TransportController extends BaseController
{
    protected $transportModel;
    protected $countryModel;
    protected $stateModel;

    public function __construct()
    {
        $this->transportModel = new TransportModel();
        $this->countryModel = new CountryModel();
        $this->stateModel = new StateModel();
    }

    public function index()
    {
        $data['transports'] = $this->transportModel
            ->select('transports.*, states.name as state_name, countries.name as country_name')
            ->join('states', 'states.id = transports.state_id', 'left')
            ->join('countries', 'countries.id = transports.country_id', 'left')
            ->findAll();
        $data['title'] = 'Transports';
        return view('transports/index', $data);
    }

    public function view($id)
    {
        $data['transport'] = $this->transportModel->find($id);

        if (!$data['transport']) {
            return redirect()->to('transports')->with('error', 'Transport not found.');
        }

        if ($data['transport']['state_id']) {
            $data['state'] = $this->stateModel->find($data['transport']['state_id']);
        }

        if ($data['transport']['country_id']) {
            $data['country'] = $this->countryModel->find($data['transport']['country_id']);
        }

        // Get invoices using this transport
        $invoiceModel = new \App\Models\InvoiceModel();
        // Since invoice model stores transport_name as string
        $transportName = $data['transport']['transport_name'];
        
        $data['invoices'] = $invoiceModel->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id', 'left')
            ->where('transport_name', $transportName)
            ->orderBy('invoice_date', 'DESC')
            ->findAll();

        // Get return shipments using this transport
        $returnShipmentModel = new \App\Models\ReturnShipmentModel();
        
        $data['return_shipments'] = $returnShipmentModel->select('return_shipments.*, vendors.name as vendor_name')
            ->join('vendors', 'vendors.id = return_shipments.vendor_id', 'left')
            ->where('transport_name', $transportName)
            ->orderBy('return_date', 'DESC')
            ->findAll();

        $data['title'] = $data['transport']['transport_name'];
        return view('transports/view', $data);
    }

    public function create()
    {
        $countries = $this->countryModel->findAll();
        usort($countries, function($a, $b) {
            if ($a['name'] == 'India') return -1;
            if ($b['name'] == 'India') return 1;
            return strcmp($a['name'], $b['name']);
        });
        $data['countries'] = $countries;

        $india = array_values(array_filter($countries, function($c) { return $c['name'] == 'India'; }))[0] ?? null;
        $data['default_country_id'] = $india ? $india['id'] : null;
        $data['states'] = $india ? $this->stateModel->where('country_id', $india['id'])->where('status', 'active')->orderBy('name', 'ASC')->findAll() : [];

        $data['title'] = 'Add New Transport';
        return view('transports/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->transportModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->transportModel->save($this->request->getPost());
        return redirect()->to('transports')->with('success', 'Transport created successfully.');
    }

    public function edit($id)
    {
        $data['transport'] = $this->transportModel->find($id);
        if (!$data['transport']) {
            return redirect()->to('transports')->with('error', 'Transport not found.');
        }

        $countries = $this->countryModel->findAll();
        usort($countries, function($a, $b) {
            if ($a['name'] == 'India') return -1;
            if ($b['name'] == 'India') return 1;
            return strcmp($a['name'], $b['name']);
        });
        $data['countries'] = $countries;

        $selectedCountryId = $data['transport']['country_id'];
        $data['states'] = $selectedCountryId ? $this->stateModel->where('country_id', $selectedCountryId)->where('status', 'active')->orderBy('name', 'ASC')->findAll() : [];

        $data['title'] = 'Edit Transport';
        return view('transports/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate($this->transportModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->transportModel->update($id, $this->request->getPost());
        return redirect()->to('transports')->with('success', 'Transport updated successfully.');
    }

    public function delete($id)
    {
        $this->transportModel->delete($id);
        return redirect()->to('transports')->with('success', 'Transport deleted successfully.');
    }
}
