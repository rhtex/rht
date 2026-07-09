<?php

namespace App\Controllers;

use App\Models\WeaverModel;

class WeaverController extends BaseController
{
    protected $weaverModel;

    public function __construct()
    {
        $this->weaverModel = new WeaverModel();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $data['weavers'] = $this->weaverModel->getWeaversWithFilters($filters);
        $data['title'] = 'Weavers';
        $data['filters'] = $filters;

        return view('production/weavers/index', $data);
    }

    public function view($id)
    {
        $data['weaver'] = $this->weaverModel->find($id);
        if (!$data['weaver']) {
            return redirect()->to('production/weavers')->with('error', 'Weaver not found.');
        }
        $data['title'] = 'Weaver Details';

        $loomModel = new \App\Models\WeaverLoomModel();
        $data['looms'] = $loomModel->where('weaver_id', $id)->findAll();

        $weaverLedgerModel = new \App\Models\WeaverLedgerModel();
        $data['weaver_ledgers'] = $weaverLedgerModel->where('weaver_id', $id)->findAll();
        
        $weaverTxnModel = new \App\Models\WeaverLedgerTransactionModel();
        $data['weaver_transactions'] = [];
        foreach ($data['weaver_ledgers'] as $ledger) {
            $data['weaver_transactions'][$ledger['id']] = $weaverTxnModel->where('ledger_id', $ledger['id'])->orderBy('transaction_date', 'DESC')->findAll();
        }

        // Consolidated Transactions (Loom + Weaver)
        $db = \Config\Database::connect();
        $builder = $db->table('weaver_ledger_transactions wlt');
        $builder->select("wlt.transaction_date, wlt.transaction_type, wlt.amount, wlt.payment_method, wlt.reference_number, wlt.remarks, wl.title as ledger_name, 'Personal' as ledger_type");
        $builder->join('weaver_ledgers wl', 'wl.id = wlt.ledger_id');
        $builder->where('wl.weaver_id', $id);
        
        $builder2 = $db->table('loom_ledger_transactions llt');
        $builder2->select("llt.transaction_date, llt.transaction_type, llt.amount, llt.payment_method, llt.reference_number, llt.remarks, ll.title as ledger_name, CONCAT('Loom ', l.loom_number) as ledger_type");
        $builder2->join('loom_ledgers ll', 'll.id = llt.ledger_id');
        $builder2->join('weaver_looms l', 'l.id = ll.loom_id');
        $builder2->where('ll.weaver_id', $id);

        $query = $db->query($builder->getCompiledSelect() . ' UNION ALL ' . $builder2->getCompiledSelect() . ' ORDER BY transaction_date DESC, ledger_name ASC');
        $data['consolidated_txns'] = $query->getResultArray();

        $settlementModel = new \App\Models\ProductionWeaverSettlementModel();
        $data['settlements'] = $settlementModel->where('weaver_id', $id)->orderBy('settlement_date', 'DESC')->findAll();

        return view('production/weavers/view', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Weaver';
        return view('production/weavers/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->weaverModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Handle address proof upload
        $file = $this->request->getFile('address_proof');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/weavers', $newName);
            $data['address_proof'] = 'uploads/weavers/' . $newName;
        }

        $data['created_by'] = session('user_id');

        $this->weaverModel->save($data);
        return redirect()->to('production/weavers')->with('success', 'Weaver created successfully.');
    }

    public function edit($id)
    {
        $data['weaver'] = $this->weaverModel->find($id);
        if (!$data['weaver']) {
            return redirect()->to('production/weavers')->with('error', 'Weaver not found.');
        }
        $data['title'] = 'Edit Weaver';
        return view('production/weavers/form', $data);
    }

    public function update($id)
    {
        $rules = $this->weaverModel->getValidationRules();
        if (isset($rules['code'])) {
            $rules['code'] = str_replace('{id}', $id, $rules['code']);
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Handle address proof upload
        $file = $this->request->getFile('address_proof');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old file if exists
            $weaver = $this->weaverModel->find($id);
            if (!empty($weaver['address_proof']) && file_exists(FCPATH . $weaver['address_proof'])) {
                @unlink(FCPATH . $weaver['address_proof']);
            }
            
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/weavers', $newName);
            $data['address_proof'] = 'uploads/weavers/' . $newName;
        }

        $data['updated_by'] = session('user_id');

        $this->weaverModel->update($id, $data);
        return redirect()->to('production/weavers')->with('success', 'Weaver updated successfully.');
    }

    public function delete($id)
    {
        $this->weaverModel->delete($id);
        return redirect()->to('production/weavers')->with('success', 'Weaver deleted successfully.');
    }
}
