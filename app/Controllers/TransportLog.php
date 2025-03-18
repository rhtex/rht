<?php

namespace App\Controllers;

use App\Models\TransportLogModel;
use App\Models\TransportModel;
use CodeIgniter\Controller;

class TransportLog extends Controller
{
    public function index()
    {
        $logModel = new TransportLogModel();
        $transportModel = new TransportModel();

        $data['logs'] = $logModel->select('transport_logs.*, transports.transporter_name')
                                ->join('transports', 'transports.id = transport_logs.transport_id')
                                ->findAll();
        
        return view('transport_logs/index', $data);
    }

    public function create()
    {
        $transportModel = new TransportModel();
        $data['transports'] = $transportModel->findAll();
        
        return view('transport_logs/create', $data);
    }

    public function store()
    {
        $logModel = new TransportLogModel();
        
        $data = [
            'transport_id' => $this->request->getPost('transport_id'),
            'price'        => $this->request->getPost('price'),
            'paid_by'      => $this->request->getPost('paid_by'),
            'transport_no' => $this->request->getPost('transport_no'),
            'contains'     => $this->request->getPost('contains'),
        ];

        $logModel->save($data);
        return redirect()->to('/transportlog');
    }

    public function delete($id)
    {
        $logModel = new TransportLogModel();
        $logModel->delete($id);
        
        return redirect()->to('/transportlog');
    }
}
