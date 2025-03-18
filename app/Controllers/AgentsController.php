<?php

namespace App\Controllers;

use App\Models\AgentModel;
use CodeIgniter\Controller;

class AgentsController extends Controller
{
    // List All Agents
    public function index()
    {
        $agentModel = new AgentModel();
        $data['agents'] = $agentModel->findAll();  // Get all agents

        $data['pageTitle'] = 'Agent List';  // Set the page title

        return view('agents/list_agents', $data);
    }

    // Create Agent Form
    public function create()
    {
        $data['pageTitle'] = 'Create Agent';  // Set the page title
        return view('agents/create', $data);
    }

    // Edit Agent Form
    public function edit($id)
    {
        $agentModel = new AgentModel();
        $data['agent'] = $agentModel->find($id);

        if (!$data['agent']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Agent with ID $id not found.");
        }

        $data['pageTitle'] = 'Edit Agent';  // Set the page title

        return view('agents/edit', $data);
    }


    // View Single Agent
    public function view($id)
    {
        $agentModel = new AgentModel();
        $data['agent'] = $agentModel->find($id);

        if (!$data['agent']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Agent with ID $id not found.");
        }

        $data['pageTitle'] = 'Agent Details';  // Set the page title

        return view('agents/view_agent', $data);
    }

    // Store Agent Data
    public function store()
    {
        $data = $this->request->getPost();

        // Automatically set created_by, updated_by, created_at, updated_at
        $session = session();
        $user_id = $session->get('id'); // Assuming user_id is stored in session

        $data['created_by'] = $user_id;
        $data['updated_by'] = $user_id;  // Set both to the logged-in user initially
        $data['created_at'] = date('Y-m-d H:i:s');  // Current timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');  // Current timestamp

        // Insert agent data into the database
        $agentModel = new AgentModel();
        $agentModel->save($data);

        return redirect()->to('/agents');
    }

    // Update Agent Data
    public function update($id)
    {
        $data = $this->request->getPost();
        $data['id'] = $id;  // Ensure ID is included in the update

        // Automatically set created_by, updated_by, and timestamps
        $data['updated_by'] = session()->get('user_id');  // Assuming user_id is stored in session
        $data['updated_at'] = date('Y-m-d H:i:s');

        $agentModel = new AgentModel();
        $agentModel->save($data);

        return redirect()->to('/agents');
    }
}
