<?php

namespace App\Controllers;

use App\Models\TransportModel;
use CodeIgniter\Controller;
use App\Controllers\PermissionsController;

class TransportController extends Controller
{
    protected $permissionController;

    public function __construct()
    {
        $this->permissionController = new PermissionsController();
    }

    public function index()
    {
        $check = $this->permissionController->checkPermission('Transport', 'read');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportModel = new TransportModel();
        $data = [
            'pageTitle' => 'List Transport',
            'transports' => $transportModel->findAll()
        ];

        return view('transports/list_transport', $data);
    }

    public function create()
    {
        $check = $this->permissionController->checkPermission('Transport', 'create');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $data['pageTitle'] = 'Create Transport';
        return view('transports/create_transport', $data);
    }

    public function store()
    {
        $check = $this->permissionController->checkPermission('Transport', 'create');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportModel = new TransportModel();
        $data = $this->request->getPost([
            'gst_number', 'transporter_name', 'transport_address', 
            'branch_name', 'branch_phone', 'branch_mobile', 
            'branch_email', 'customercare_email', 'customer_care_phone'
        ]);

        $transportModel->save($data);
        return redirect()->to('/list_transport');
    }

    public function edit($id)
    {
        $check = $this->permissionController->checkPermission('Transport', 'update');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportModel = new TransportModel();
        $data = [
            'pageTitle' => 'Edit Transport',
            'transport' => $transportModel->find($id)
        ];

        return view('transports/edit_transport', $data);
    }

    public function update($id)
    {
        $check = $this->permissionController->checkPermission('Transport', 'update');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportModel = new TransportModel();
        $data = $this->request->getPost([
            'gst_number', 'transporter_name', 'transport_address', 
            'branch_name', 'branch_phone', 'branch_mobile', 
            'branch_email', 'customercare_email', 'customer_care_phone'
        ]);

        $transportModel->update($id, $data);
        return redirect()->to('/list_transport');
    }

    public function delete($id)
    {
        $check = $this->permissionController->checkPermission('Transport', 'delete');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportModel = new TransportModel();
        $transportModel->delete($id);

        return redirect()->to('/list_transport');
    }

    public function view($id)
    {
        $check = $this->permissionController->checkPermission('Transport', 'read');

        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportModel = new TransportModel();
        $data = [
            'pageTitle' => 'View Transport',
            'transport' => $transportModel->find($id)
        ];

        if (!$data['transport']) {
            return redirect()->to('/list_transport');
        }

        return view('transports/view_transport', $data);
    }
}
