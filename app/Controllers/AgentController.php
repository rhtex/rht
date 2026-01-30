<?php

namespace App\Controllers;

use App\Models\AgentModel;
use App\Models\CountryModel;
use App\Models\StateModel;

class AgentController extends BaseController
{
    protected $agentModel;
    protected $countryModel;
    protected $stateModel;

    public function __construct()
    {
        $this->agentModel = new AgentModel();
        $this->countryModel = new CountryModel();
        $this->stateModel = new StateModel();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
        ];

        $data['agents'] = $this->agentModel->getAgentsWithFilters($filters);
        $data['title'] = 'Agents';
        $data['filters'] = $filters;

        return view('agents/index', $data);
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

        $data['title'] = 'Add New Agent';
        return view('agents/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->agentModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Handle KYC Uploads
        $uploadPath = ROOTPATH . 'public/uploads/agents/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        foreach (['address_proof_front', 'address_proof_back'] as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                $data[$field] = $newName;
            }
        }

        $this->agentModel->save($data);
        return redirect()->to('agents')->with('success', 'Agent created successfully.');
    }

    public function edit($id)
    {
        $data['agent'] = $this->agentModel->find($id);
        if (!$data['agent']) {
            return redirect()->to('agents')->with('error', 'Agent not found.');
        }

        $countries = $this->countryModel->findAll();
        usort($countries, function($a, $b) {
            if ($a['name'] == 'India') return -1;
            if ($b['name'] == 'India') return 1;
            return strcmp($a['name'], $b['name']);
        });
        $data['countries'] = $countries;

        $selectedCountryId = $data['agent']['country_id'];
        $data['states'] = $selectedCountryId ? $this->stateModel->where('country_id', $selectedCountryId)->where('status', 'active')->orderBy('name', 'ASC')->findAll() : [];

        $data['title'] = 'Edit Agent';
        return view('agents/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate($this->agentModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $existing = $this->agentModel->find($id);

        // Handle KYC Uploads
        $uploadPath = ROOTPATH . 'public/uploads/agents/';
        foreach (['address_proof_front', 'address_proof_back'] as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Delete old file
                if (!empty($existing[$field]) && file_exists($uploadPath . $existing[$field])) {
                    unlink($uploadPath . $existing[$field]);
                }
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                $data[$field] = $newName;
            }
        }

        $this->agentModel->update($id, $data);
        return redirect()->to('agents')->with('success', 'Agent updated successfully.');
    }

    public function view($id)
    {
        $data['agent'] = $this->agentModel
            ->select('agents.*, states.name as state_name, countries.name as country_name')
            ->join('states', 'states.id = agents.state_id', 'left')
            ->join('countries', 'countries.id = agents.country_id', 'left')
            ->find($id);
            
        if (!$data['agent']) {
            return redirect()->to('agents')->with('error', 'Agent not found.');
        }

        $data['title'] = 'Agent Details - ' . $data['agent']['agent_name'];
        return view('agents/view', $data);
    }

    public function delete($id)
    {
        $existing = $this->agentModel->find($id);
        if ($existing) {
            $uploadPath = ROOTPATH . 'public/uploads/agents/';
            foreach (['address_proof_front', 'address_proof_back'] as $field) {
                if (!empty($existing[$field]) && file_exists($uploadPath . $existing[$field])) {
                    unlink($uploadPath . $existing[$field]);
                }
            }
            $this->agentModel->delete($id);
        }
        return redirect()->to('agents')->with('success', 'Agent deleted successfully.');
    }
}
