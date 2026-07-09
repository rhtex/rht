<?php

namespace App\Controllers;

use App\Models\ProductionAllocationModel;
use App\Models\ProductionAllocationWeftModel;
use App\Models\WeaverModel;
use App\Models\WeaverLoomModel;
use App\Models\YarnBeamModel;
use App\Models\ProductionYarnModel;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\YarnStockMovementModel;

class WarpAllocationController extends BaseController
{
    protected $allocationModel;
    protected $allocationWeftModel;
    protected $weaverModel;
    protected $loomModel;
    protected $beamModel;
    protected $yarnModel;
    protected $stockMovementModel;

    public function __construct()
    {
        $this->allocationModel = new ProductionAllocationModel();
        $this->allocationWeftModel = new ProductionAllocationWeftModel();
        $this->weaverModel = new WeaverModel();
        $this->loomModel = new WeaverLoomModel();
        $this->beamModel = new YarnBeamModel();
        $this->yarnModel = new ProductionYarnModel();
        $this->stockMovementModel = new YarnStockMovementModel();
    }

    public function index()
    {
        $allocations = $this->allocationModel
            ->select('production_allocations.*, weavers.name as weaver_name, weaver_looms.loom_number, production_beams.beam_number')
            ->join('weavers', 'weavers.id = production_allocations.weaver_id')
            ->join('weaver_looms', 'weaver_looms.id = production_allocations.loom_id', 'left')
            ->join('production_beams', 'production_beams.id = production_allocations.warp_beam_id', 'left')
            ->orderBy('id', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Warp & Weft Allocations',
            'allocations' => $allocations
        ];

        return view('production/warp_allocations/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'New Warp & Weft Allocation',
            'weavers' => $this->weaverModel->where('status', 'active')->findAll(),
            'beams' => $this->beamModel->where('status', 'Loaded')->findAll(),
            'yarns' => $this->yarnModel->where('status', 'active')->where('stock_kg >', 0)->findAll()
        ];
        return view('production/warp_allocations/form', $data);
    }

    public function store()
    {
        $weaverId = $this->request->getPost('weaver_id');
        $loomId = $this->request->getPost('loom_id');
        $beamId = $this->request->getPost('beam_id'); // Optional
        $wefts = $this->request->getPost('wefts'); // Optional array
        
        $expectedReturnDate = $this->request->getPost('expected_return_date');
        $remarks = $this->request->getPost('remarks');

        // Validation: At least a beam or a weft item must be provided
        if (empty($beamId) && empty($wefts)) {
            return redirect()->back()->withInput()->with('error', 'You must allocate at least a Warp Beam or Weft Yarn.');
        }

        if (!$weaverId || !$loomId) {
            return redirect()->back()->withInput()->with('error', 'Weaver and Loom are required.');
        }

        $loom = $this->loomModel->find($loomId);
        $weaver = $this->weaverModel->find($weaverId);

        if (!$loom || !$weaver) {
            return redirect()->back()->withInput()->with('error', 'Invalid Weaver or Loom.');
        }

        $contractType = $loom['contract_type'];

        // Generate Allocation Number
        $allocationNumber = 'WA-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $db = \Config\Database::connect();
        $db->transStart();

        $allocationData = [
            'allocation_number' => $allocationNumber,
            'allocation_date' => date('Y-m-d'),
            'weaver_id' => $weaverId,
            'loom_id' => $loomId,
            'warp_beam_id' => $beamId ?: null,
            'contract_type' => $contractType,
            'expected_return_date' => $expectedReturnDate,
            'status' => 'Allocated',
            'remarks' => $remarks,
            'created_by' => session('user_id'),
        ];

        // Process based on contract type
        if ($contractType == 'Sale & Buy Back') {
            if (!$weaver['customer_id']) {
                return redirect()->back()->withInput()->with('error', 'Customer profile is required for Weaver under Sale & Buy Back contract.');
            }
            
            // Create Sales Invoice Header
            $invoiceModel = new InvoiceModel();
            $invoiceItemModel = new InvoiceItemModel();
            
            $invoiceNumber = $invoiceModel->generateInvoiceNumber();
            $invoiceData = [
                'customer_id' => $weaver['customer_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'Open',
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'balance' => 0,
                'created_by' => session('user_id')
            ];

            $invoiceId = $invoiceModel->insert($invoiceData);

            $allocationData['reference_document_type'] = 'Sales Invoice';
            $allocationData['reference_document_id'] = $invoiceId;
        } else {
            // Job Work
            $allocationData['reference_document_type'] = 'DC';
            $allocationData['reference_document_id'] = null; // Sticking to the allocation id itself as DC
        }

        $allocationId = $this->allocationModel->insert($allocationData);
        $totalInvoiceAmount = 0;

        // Process Beam (Warp) if selected
        if (!empty($beamId)) {
            $this->beamModel->update($beamId, [
                'status' => 'In Weaving',
                'current_holder' => $weaver['name']
            ]);

            if ($contractType == 'Sale & Buy Back') {
                $beamCost = 5000; // Placeholder for beam cost
                $invoiceItemModel->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => null,
                    'item_name' => 'Loaded Warp Beam - ' . $allocationNumber,
                    'quantity' => 1,
                    'rate' => $beamCost,
                    'amount' => $beamCost,
                    'tax_percentage' => 0
                ]);
                $totalInvoiceAmount += $beamCost;
            }
        }

        // Process Weft Yarns if selected
        if (!empty($wefts)) {
            foreach ($wefts as $weft) {
                if (empty($weft['yarn_id']) || empty($weft['issued_weight'])) continue;
                
                $weight = (float) $weft['issued_weight'];
                $rate = (float) ($weft['rate'] ?? 0);
                $yarn = $this->yarnModel->find($weft['yarn_id']);

                if (!$yarn || $yarn['stock_kg'] < $weight) {
                    return redirect()->back()->withInput()->with('error', 'Insufficient stock for yarn: ' . ($yarn['name'] ?? 'Unknown'));
                }

                // Insert into wefts allocation table
                $this->allocationWeftModel->insert([
                    'allocation_id' => $allocationId,
                    'yarn_id' => $weft['yarn_id'],
                    'color_id' => null, // Ignoring colors for simplicity here
                    'batch_number' => $weft['batch_number'] ?? '',
                    'issued_weight' => $weight,
                    'rate' => $rate
                ]);

                // Reduce stock
                $this->yarnModel->update($yarn['id'], ['stock_kg' => $yarn['stock_kg'] - $weight]);

                // Add stock movement
                $this->stockMovementModel->insert([
                    'yarn_id' => $yarn['id'],
                    'transaction_type' => 'Issue',
                    'reference_type' => 'Warp Allocation',
                    'reference_id' => $allocationId,
                    'quantity' => $weight,
                    'movement_date' => date('Y-m-d H:i:s'),
                    'remarks' => 'Issued to ' . $weaver['name'] . ' for ' . $contractType,
                    'created_by' => session('user_id')
                ]);

                if ($contractType == 'Sale & Buy Back') {
                    $amount = $weight * $rate;
                    $invoiceItemModel->insert([
                        'invoice_id' => $invoiceId,
                        'product_id' => null, // we map to text
                        'item_name' => 'Weft Yarn - ' . $yarn['name'],
                        'quantity' => $weight,
                        'rate' => $rate,
                        'amount' => $amount,
                        'tax_percentage' => 0
                    ]);
                    $totalInvoiceAmount += $amount;
                }
            }
        }

        if ($contractType == 'Sale & Buy Back') {
            $invoiceModel->update($invoiceId, [
                'subtotal' => $totalInvoiceAmount,
                'total_amount' => $totalInvoiceAmount,
                'balance' => $totalInvoiceAmount
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to allocate Warp/Weft. Please try again.');
        }

        return redirect()->to('production/warp-allocations')->with('success', 'Warp/Weft successfully allocated.');
    }

    public function view($id)
    {
        $allocation = $this->allocationModel
            ->select('production_allocations.*, weavers.name as weaver_name, weaver_looms.loom_number, production_beams.beam_number')
            ->join('weavers', 'weavers.id = production_allocations.weaver_id')
            ->join('weaver_looms', 'weaver_looms.id = production_allocations.loom_id', 'left')
            ->join('production_beams', 'production_beams.id = production_allocations.warp_beam_id', 'left')
            ->find($id);

        if (!$allocation) {
            return redirect()->to('production/warp-allocations')->with('error', 'Allocation not found.');
        }

        $wefts = $this->allocationWeftModel
            ->select('production_allocation_wefts.*, production_yarns.name as yarn_name')
            ->join('production_yarns', 'production_yarns.id = production_allocation_wefts.yarn_id')
            ->where('allocation_id', $id)
            ->findAll();

        $data = [
            'title' => 'View Warp & Weft Allocation',
            'allocation' => $allocation,
            'wefts' => $wefts
        ];

        return view('production/warp_allocations/view', $data);
    }
    
    public function delete($id)
    {
        $allocation = $this->allocationModel->find($id);
        if (!$allocation) {
            return redirect()->back()->with('error', 'Allocation not found.');
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        // Revert Beam Status
        if (!empty($allocation['warp_beam_id'])) {
            $this->beamModel->update($allocation['warp_beam_id'], [
                'status' => 'Loaded',
                'current_holder' => 'Company'
            ]);
        }
        
        // Revert Weft Stock
        $wefts = $this->allocationWeftModel->where('allocation_id', $id)->findAll();
        foreach ($wefts as $weft) {
            $yarn = $this->yarnModel->find($weft['yarn_id']);
            if ($yarn) {
                $this->yarnModel->update($yarn['id'], ['stock_kg' => $yarn['stock_kg'] + $weft['issued_weight']]);
                
                // Reverse Stock movement
                $this->stockMovementModel->insert([
                    'yarn_id' => $yarn['id'],
                    'transaction_type' => 'Return',
                    'reference_type' => 'Warp Allocation Cancelled',
                    'reference_id' => $id,
                    'quantity' => $weft['issued_weight'],
                    'movement_date' => date('Y-m-d H:i:s'),
                    'remarks' => 'Allocation Cancelled',
                    'created_by' => session('user_id')
                ]);
            }
        }
        
        // If Sale & Buy Back, cancel the invoice
        if ($allocation['reference_document_type'] == 'Sales Invoice') {
            $invoiceModel = new InvoiceModel();
            $invoiceModel->update($allocation['reference_document_id'], ['status' => 'Cancelled']);
        }
        
        $this->allocationWeftModel->where('allocation_id', $id)->delete();
        $this->allocationModel->delete($id);
        
        $db->transComplete();
        
        return redirect()->to('production/warp-allocations')->with('success', 'Allocation deleted successfully.');
    }
}
