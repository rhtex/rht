<?php

namespace App\Controllers;

use App\Models\ColorModel;

class ColorController extends BaseController
{
    protected $colorModel;

    public function __construct()
    {
        $this->colorModel = new ColorModel();
    }

    public function index()
    {
        $data['colors'] = $this->colorModel->orderBy('id', 'DESC')->findAll();
        $data['title'] = 'Color Management';
        return view('production/colors/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Color';
        return view('production/colors/form', $data);
    }

    public function store()
    {
        $data = [
            'name'          => $this->request->getPost('name'),
            'code'          => $this->request->getPost('code'),
            'color_palette' => $this->request->getPost('color_palette'),
            'status'        => $this->request->getPost('status') ?: 'Active',
        ];

        if (!$this->colorModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->colorModel->errors());
        }

        return redirect()->to('production/colors')->with('success', 'Color added successfully.');
    }

    public function edit($id)
    {
        $data['color'] = $this->colorModel->find($id);
        if (!$data['color']) {
            return redirect()->to('production/colors')->with('error', 'Color not found.');
        }
        $data['title'] = 'Edit Color';
        return view('production/colors/form', $data);
    }

    public function update($id)
    {
        $data = [
            'id'            => $id,
            'name'          => $this->request->getPost('name'),
            'code'          => $this->request->getPost('code'),
            'color_palette' => $this->request->getPost('color_palette'),
            'status'        => $this->request->getPost('status'),
        ];

        if (!$this->colorModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->colorModel->errors());
        }

        return redirect()->to('production/colors')->with('success', 'Color updated successfully.');
    }

    public function delete($id)
    {
        $color = $this->colorModel->find($id);
        if (!$color) {
            return redirect()->to('production/colors')->with('error', 'Color not found.');
        }

        $this->colorModel->delete($id);
        return redirect()->to('production/colors')->with('success', 'Color deleted successfully.');
    }
}
