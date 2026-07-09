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

        $db = \Config\Database::connect();
        $db->transStart();

        if ($this->loomModel->save($data)) {
            $loomId = $this->loomModel->getInsertID();
            $weaverId = $data['weaver_id'];
            
            $ledgerModel = new \App\Models\LoomLedgerModel();
            $transactionModel = new \App\Models\LoomLedgerTransactionModel();

            // Loom Owner Loan
            if (isset($data['loom_owner']) && $data['loom_owner'] == 'Company' && isset($data['loom_cost']) && $data['loom_cost'] > 0) {
                $this->createAutoLedger($ledgerModel, $transactionModel, $loomId, $weaverId, 'Loom Purchase', $data['loom_cost']);
            }
            
            // Jacquard Owner Loan
            if (isset($data['jacquard_owner']) && $data['jacquard_owner'] == 'Company' && isset($data['jacquard_cost']) && $data['jacquard_cost'] > 0) {
                $this->createAutoLedger($ledgerModel, $transactionModel, $loomId, $weaverId, 'Jacquard Purchase', $data['jacquard_cost']);
            }
            
            // Fitting Loan
            if (isset($data['fitted_by']) && $data['fitted_by'] == 'Company' && isset($data['fitting_cost']) && $data['fitting_cost'] > 0) {
                $this->createAutoLedger($ledgerModel, $transactionModel, $loomId, $weaverId, 'Fitting Advance', $data['fitting_cost']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                 return redirect()->back()->with('error', 'Failed to add loom.');
            }

            return redirect()->back()->with('success', 'Loom added successfully, and ledgers automatically created.');
        }

        return redirect()->back()->withInput()->with('errors', $this->loomModel->errors());
    }

    private function createAutoLedger($ledgerModel, $transactionModel, $loomId, $weaverId, $title, $principalAmount)
    {
        $ledgerData = [
            'loom_id' => $loomId,
            'weaver_id' => $weaverId,
            'title' => $title,
            'principal_amount' => $principalAmount,
            'interest_rate' => 0,
            'total_amount_due' => $principalAmount,
            'balance_amount' => $principalAmount,
            'status' => 'Active',
            'created_by' => session('user_id'),
        ];
        
        if ($ledgerModel->insert($ledgerData)) {
            $ledgerId = $ledgerModel->getInsertID();
            
            $transactionData = [
                'ledger_id' => $ledgerId,
                'transaction_type' => 'Principal',
                'amount' => $principalAmount,
                'transaction_date' => date('Y-m-d'),
                'remarks' => 'Auto-generated ' . $title,
                'created_by' => session('user_id'),
            ];
            $transactionModel->insert($transactionData);
        }
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

    public function view($id)
    {
        $loom = $this->loomModel->find($id);
        if (!$loom) {
            return redirect()->back()->with('error', 'Loom not found.');
        }

        $weaverModel = new \App\Models\WeaverModel();
        $weaver = $weaverModel->find($loom['weaver_id']);

        $allocationModel = new \App\Models\ProductionAllocationModel();
        $allocations = $allocationModel->where('loom_id', $id)->findAll();

        $ledgerModel = new \App\Models\LoomLedgerModel();
        $ledgers = $ledgerModel->where('loom_id', $id)->findAll();
        
        $transactionModel = new \App\Models\LoomLedgerTransactionModel();
        $transactions = [];
        foreach ($ledgers as $ledger) {
            $transactions[$ledger['id']] = $transactionModel->where('ledger_id', $ledger['id'])->orderBy('transaction_date', 'DESC')->findAll();
        }

        return view('production/looms/view', [
            'loom'         => $loom,
            'weaver'       => $weaver,
            'allocations'  => $allocations,
            'ledgers'      => $ledgers,
            'transactions' => $transactions,
        ]);
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
