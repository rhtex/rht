<?php

namespace App\Controllers;

use App\Models\TransportLogModel;
use App\Models\TransportModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use App\Controllers\PermissionsController;

class TransportLogsController extends Controller
{
    protected $permissionController;

    public function __construct()
    {
        $this->permissionController = new PermissionsController();
    }

    public function index()
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'read');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportLogModel = new TransportLogModel();
        $data = [
            'pageTitle' => 'List Incoming Courier',
            'transportLogs' => $transportLogModel->where('transport_type', '1')->findAll()
        ];

        return view('transport_logs/list_courier_in', $data);
    }

    public function list_courier_out()
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'read');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportLogModel = new TransportLogModel();
        $data = [
            'pageTitle' => 'List Outgoing Courier',
            'transportLogs' => $transportLogModel->where('transport_type', '2')->findAll()
        ];

        return view('transport_logs/list_courier_out', $data);
    }

    public function add_new_courier_in()
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'create');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $data['pageTitle'] = 'Add New Incoming Courier';
        $transportModel = new TransportModel();
        $usersModel = new UserModel();
        $data['transports'] = $transportModel->findAll();
        $data['users'] = $usersModel->findAll();

        return view('transport_logs/courier_in', $data);
    }

    public function edit_courier_in($id)
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'update');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportLogModel = new TransportLogModel();
        $transportLog = $transportLogModel->find($id);

        if (!$transportLog) {
            return redirect()->to('/list_courier_in')->with('error', 'Transport log not found.');
        }

        $transportModel = new TransportModel();
        $usersModel = new UserModel();
        $data = [
            'pageTitle' => 'Edit Incoming Courier',
            'log' => $transportLog,
            'transports' => $transportModel->findAll(),
            'users' => $usersModel->findAll()
        ];

        return view('transport_logs/courier_in_edit', $data);
    }

    public function add_new_courier_out()
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'create');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $data['pageTitle'] = 'Add New Outgoing Courier';
        $transportModel = new TransportModel();
        $usersModel = new UserModel();
        $data['transports'] = $transportModel->findAll();
        $data['users'] = $usersModel->findAll();

        return view('transport_logs/courier_out', $data);
    }

    public function edit_courier_out($id)
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'update');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportLogModel = new TransportLogModel();
        $transportLog = $transportLogModel->find($id);

        if (!$transportLog) {
            return redirect()->to('/list_courier_out')->with('error', 'Transport log not found.');
        }

        $transportModel = new TransportModel();
        $usersModel = new UserModel();
        $data = [
            'pageTitle' => 'Edit Outgoing Courier',
            'log' => $transportLog,
            'transports' => $transportModel->findAll(),
            'users' => $usersModel->findAll()
        ];

        return view('transport_logs/courier_out_edit', $data);
    }

    public function delete($id)
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'delete');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportLogModel = new TransportLogModel();
        $transportLog = $transportLogModel->find($id);

        if (!$transportLog) {
            return redirect()->to('/list_courier_in')->with('error', 'Transport log not found.');
        }

        $transportLogModel->delete($id);
        return redirect()->to('/list_courier_in')->with('success', 'Transport log deleted successfully.');
    }

    public function update($id)
    {
        $check = $this->permissionController->checkPermission('TransportLogs', 'update');
        if ($check === 'No') {
            return view('signin', ['pageTitle' => 'Sign In']);
        } elseif (!$check) {
            return view('access_denied', ['pageTitle' => 'Access Denied']);
        }

        $transportLogsModel = new TransportLogModel();
        $data = $this->request->getPost([
            'sender', 'receiver', 'received_by', 'price', 'paid_by', 'transport_no', 
            'contains', 'others_reason', 'transport_id', 'sent_date', 'delivered_date', 
            'delivery_status', 'parcel_type', 'transport_type', 'booked_by'
        ]);

        $transportLogsModel->update($id, $data);
        return redirect()->to('/list_courier_in');
    }
}
