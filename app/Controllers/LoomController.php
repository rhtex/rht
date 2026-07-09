<?php

namespace App\Controllers;

use App\Models\WeaverLoomModel;

class LoomController extends BaseController
{
    protected $loomModel;

    public function __construct()
    {
        $this->loomModel = new WeaverLoomModel();
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['created_by'] = session('user_id');

        if ($this->loomModel->save($data)) {
            return redirect()->back()->with('success', 'Loom added successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $this->loomModel->errors());
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $data['updated_by'] = session('user_id');

        if ($this->loomModel->update($id, $data)) {
            return redirect()->back()->with('success', 'Loom updated successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $this->loomModel->errors());
    }

    public function delete($id)
    {
        $this->loomModel->delete($id);
        return redirect()->back()->with('success', 'Loom deleted successfully.');
    }

    public function getLoomsByWeaver($weaverId)
    {
        $looms = $this->loomModel->where('weaver_id', $weaverId)
                                 ->where('status', 'Active')
                                 ->findAll();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $looms
        ]);
    }
}
