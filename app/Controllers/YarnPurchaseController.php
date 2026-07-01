<?php

namespace App\Controllers;

use App\Models\YarnPurchaseModel;
use App\Models\YarnStockMovementModel;

class YarnPurchaseController extends BaseController
{
    protected $purchaseModel;
    protected $movementModel;

    public function __construct()
    {
        $this->purchaseModel = new YarnPurchaseModel();
        $this->movementModel = new YarnStockMovementModel();
    }

    public function index()
    {
        $data['purchases'] = $this->purchaseModel->orderBy('purchase_date', 'DESC')->findAll();
        $data['title'] = 'Yarn Purchases';
        return view('production/yarn_purchases/index', $data);
    }

    public function view($id)
    {
        $data['purchase'] = $this->purchaseModel->find($id);
        if (!$data['purchase']) {
            return redirect()->to('production/yarn-purchases')->with('error', 'Purchase record not found.');
        }
        $data['title'] = 'Yarn Purchase Details';
        return view('production/yarn_purchases/view', $data);
    }

    public function create()
    {
        $data['title'] = 'Add Yarn Purchase';
        return view('production/yarn_purchases/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->purchaseModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['created_by'] = session('user_id');

        $db = \Config\Database::connect();
        $db->transStart();

        $this->purchaseModel->save($data);
        $purchaseId = $this->purchaseModel->getInsertID();

        // Calculate Landed Cost per Kg (Exclusive of Tax)
        $totalWeight = (float)$data['total_weight_kg'];
        $rate = (float)$data['rate_per_kg'];
        $transport = (float)($data['transport_charges'] ?? 0);
        $other = (float)($data['other_charges'] ?? 0);

        $baseCost = $totalWeight * $rate;
        $totalLandedCost = $baseCost + $transport + $other;
        $costPerKg = $totalWeight > 0 ? ($totalLandedCost / $totalWeight) : 0;

        // Register raw yarn stock increase in ledger
        $this->movementModel->save([
            'yarn_name'     => $data['material_type'],
            'yarn_count'    => $data['yarn_count'],
            'yarn_type'     => 'Raw',
            'color'         => 'Raw',
            'brand_mill'    => $data['mill_name'],
            'lot_number'    => $data['lot_number'] ?? null,
            'csp'           => $data['csp'] ?? null,
            'warp_weft'     => $data['warp_weft'],
            'quantity_kg'   => $totalWeight,
            'quantity_cones'=> (int)($data['number_cones'] ?? 0),
            'cost_per_kg'   => $costPerKg,
            'warehouse'     => $data['warehouse_location'] ?? 'Main Warehouse',
            'movement_type' => 'Purchase',
            'reference_id'  => $purchaseId,
            'remarks'       => 'Yarn purchase. Supplier: ' . $data['supplier'] . ', Invoice: ' . $data['invoice_number'],
            'created_by'    => session('user_id'),
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save purchase.');
        }

        return redirect()->to('production/yarn-purchases')->with('success', 'Yarn purchase saved successfully.');
    }

    public function edit($id)
    {
        $data['purchase'] = $this->purchaseModel->find($id);
        if (!$data['purchase']) {
            return redirect()->to('production/yarn-purchases')->with('error', 'Purchase record not found.');
        }
        $data['title'] = 'Edit Yarn Purchase';
        return view('production/yarn_purchases/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate($this->purchaseModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['updated_by'] = session('user_id');

        $db = \Config\Database::connect();
        $db->transStart();

        $this->purchaseModel->update($id, $data);

        // Calculate Landed Cost per Kg (Exclusive of Tax)
        $totalWeight = (float)$data['total_weight_kg'];
        $rate = (float)$data['rate_per_kg'];
        $transport = (float)($data['transport_charges'] ?? 0);
        $other = (float)($data['other_charges'] ?? 0);

        $baseCost = $totalWeight * $rate;
        $totalLandedCost = $baseCost + $transport + $other;
        $costPerKg = $totalWeight > 0 ? ($totalLandedCost / $totalWeight) : 0;

        // Delete previous movement and insert updated one
        $this->movementModel->where('movement_type', 'Purchase')
                            ->where('reference_id', $id)
                            ->delete();

        $this->movementModel->save([
            'yarn_name'     => $data['material_type'],
            'yarn_count'    => $data['yarn_count'],
            'yarn_type'     => 'Raw',
            'color'         => 'Raw',
            'brand_mill'    => $data['mill_name'],
            'lot_number'    => $data['lot_number'] ?? null,
            'csp'           => $data['csp'] ?? null,
            'warp_weft'     => $data['warp_weft'],
            'quantity_kg'   => $totalWeight,
            'quantity_cones'=> (int)($data['number_cones'] ?? 0),
            'cost_per_kg'   => $costPerKg,
            'warehouse'     => $data['warehouse_location'] ?? 'Main Warehouse',
            'movement_type' => 'Purchase',
            'reference_id'  => $id,
            'remarks'       => 'Updated Yarn purchase. Supplier: ' . $data['supplier'] . ', Invoice: ' . $data['invoice_number'],
            'created_by'    => session('user_id'),
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update purchase.');
        }

        return redirect()->to('production/yarn-purchases')->with('success', 'Yarn purchase updated successfully.');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $this->purchaseModel->delete($id);
        $this->movementModel->where('movement_type', 'Purchase')
                            ->where('reference_id', $id)
                            ->delete();

        $db->transComplete();

        return redirect()->to('production/yarn-purchases')->with('success', 'Purchase record deleted successfully.');
    }
}
