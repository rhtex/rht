<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModuleModel;

class ModuleController extends BaseController
{
    protected $moduleModel;

    public function __construct()
    {
        $this->moduleModel = new ModuleModel();
    }

    public function index()
    {
        $data['modules'] = $this->moduleModel->findAll();
        return view('modules/index', $data);
    }

    public function create()
    {
        return view('modules/create');
    }

    public function store()
    {
        if (!$this->moduleModel->insert($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->moduleModel->errors());
        }

        return redirect()->to('modules')->with('success', 'Module created successfully.');
    }

    public function edit($id)
    {
        $data['module'] = $this->moduleModel->find($id);
        if (!$data['module']) {
            return redirect()->to('modules')->with('error', 'Module not found.');
        }
        return view('modules/edit', $data);
    }

    public function update($id)
    {
        if (!$this->moduleModel->update($id, $this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->moduleModel->errors());
        }

        return redirect()->to('modules')->with('success', 'Module updated successfully.');
    }

    public function delete($id)
    {
        // Check if permissions are linked
        $db = \Config\Database::connect();
        if ($db->table('permissions')->where('module_id', $id)->countAllResults() > 0) {
            return redirect()->to('modules')->with('error', 'Cannot delete module because it has permissions linked.');
        }

        $this->moduleModel->delete($id);
        return redirect()->to('modules')->with('success', 'Module deleted successfully.');
    }
}
