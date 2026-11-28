<?php

namespace App\Controllers;

use App\Models\BillModel;
use App\Models\BillItemModel;
use App\Models\PaymentModel;
use App\Models\VendorModel;
use App\Models\ProductModel;
use App\Models\BankAccountModel;

class BillController extends BaseController
{
    protected $billModel;
    protected $itemModel;
    protected $paymentModel;
    protected $vendorModel;
    protected $productModel;
    protected $bankAccountModel;
    protected $taxModel;
    protected $accountingModel;

    public function __construct()
    {
        $this->billModel = new BillModel();
        $this->itemModel = new BillItemModel();
        $this->paymentModel = new PaymentModel();
        $this->vendorModel = new VendorModel();
        $this->productModel = new ProductModel();
        $this->bankAccountModel = new BankAccountModel();
        $this->taxModel = new \App\Models\TaxModel();
        $this->accountingModel = new \App\Models\AccountingModel();
    }

    public function index()
    {
        $filters = [
            'bill_number' => $this->request->getGet('bill_number'),
            'vendor_id'   => $this->request->getGet('vendor_id'),
            'status'      => $this->request->getGet('status'),
            'date_from'   => $this->request->getGet('date_from'),
            'date_to'     => $this->request->getGet('date_to'),
        ];

        // Overall aggregate stats across all bills (or filtered by other criteria)
        $summaryBuilder = $this->billModel->select('
            COUNT(*) as total_count,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(paid_amount), 0) as paid_amount,
            COALESCE(SUM(balance), 0) as total_balance,
            COUNT(CASE WHEN (bills.status IN (\'Open\', \'Partially Paid\', \'Overdue\') OR bills.balance > 0.01) AND bills.status != \'Void\' THEN 1 END) as unpaid_count,
            COALESCE(SUM(CASE WHEN (bills.status IN (\'Open\', \'Partially Paid\', \'Overdue\') OR bills.balance > 0.01) AND bills.status != \'Void\' THEN bills.balance ELSE 0 END), 0) as unpaid_amount,
            COUNT(CASE WHEN bills.due_date = CURDATE() AND bills.balance > 0.01 AND bills.status != \'Void\' THEN 1 END) as due_today_count,
            COALESCE(SUM(CASE WHEN bills.due_date = CURDATE() AND bills.balance > 0.01 AND bills.status != \'Void\' THEN bills.balance ELSE 0 END), 0) as due_today_amount,
            COUNT(CASE WHEN bills.due_date >= CURDATE() AND bills.due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND bills.balance > 0.01 AND bills.status != \'Void\' THEN 1 END) as due_30_count,
            COALESCE(SUM(CASE WHEN bills.due_date >= CURDATE() AND bills.due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND bills.balance > 0.01 AND bills.status != \'Void\' THEN bills.balance ELSE 0 END), 0) as due_30_amount,
            COUNT(CASE WHEN (bills.status = \'Overdue\' OR bills.due_date < CURDATE()) AND bills.balance > 0.01 AND bills.status != \'Void\' THEN 1 END) as overdue_count,
            COALESCE(SUM(CASE WHEN (bills.status = \'Overdue\' OR bills.due_date < CURDATE()) AND bills.balance > 0.01 AND bills.status != \'Void\' THEN bills.balance ELSE 0 END), 0) as overdue_amount,
            COUNT(CASE WHEN bills.status = \'Open\' THEN 1 END) as open_count,
            COALESCE(SUM(CASE WHEN bills.status = \'Open\' THEN bills.balance ELSE 0 END), 0) as open_amount,
            COUNT(CASE WHEN bills.status = \'Partially Paid\' THEN 1 END) as partially_paid_count,
            COALESCE(SUM(CASE WHEN bills.status = \'Partially Paid\' THEN bills.balance ELSE 0 END), 0) as partially_paid_amount,
            COUNT(CASE WHEN bills.status = \'Paid\' THEN 1 END) as paid_count,
            COALESCE(SUM(CASE WHEN bills.status = \'Paid\' THEN bills.paid_amount ELSE 0 END), 0) as paid_total
        ', false);
        if (!empty($filters['bill_number'])) {
            $summaryBuilder->groupStart()
                ->like('bills.bill_number', trim($filters['bill_number']))
                ->orLike('bills.reference_number', trim($filters['bill_number']))
                ->groupEnd();
        }
        if (!empty($filters['vendor_id'])) {
            $summaryBuilder->where('vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Unpaid') {
                $summaryBuilder->groupStart()
                    ->whereIn('bills.status', ['Open', 'Partially Paid', 'Overdue'])
                    ->orGroupStart()
                        ->where('bills.balance >', 0.01)
                        ->where('bills.status !=', 'Void')
                    ->groupEnd()
                ->groupEnd();
            } elseif ($filters['status'] === 'DueToday') {
                $summaryBuilder->where('bills.due_date', date('Y-m-d'))
                    ->where('bills.balance >', 0.01)
                    ->where('bills.status !=', 'Void');
            } elseif ($filters['status'] === 'DueWithin30') {
                $summaryBuilder->where('bills.due_date >=', date('Y-m-d'))
                    ->where('bills.due_date <=', date('Y-m-d', strtotime('+30 days')))
                    ->where('bills.balance >', 0.01)
                    ->where('bills.status !=', 'Void');
            } elseif ($filters['status'] === 'Overdue') {
                $summaryBuilder->groupStart()
                    ->where('bills.status', 'Overdue')
                    ->orGroupStart()
                        ->where('bills.due_date <', date('Y-m-d'))
                        ->where('bills.balance >', 0.01)
                        ->where('bills.status !=', 'Void')
                    ->groupEnd()
                ->groupEnd();
            } else {
                $summaryBuilder->where('status', $filters['status']);
            }
        }
        if (!empty($filters['date_from'])) {
            $summaryBuilder->where('bill_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $summaryBuilder->where('bill_date <=', $filters['date_to']);
        }
        $totals = $summaryBuilder->first();

        // If AJAX request asking for more rows (infinite scroll)
        if ($this->request->isAJAX() && $this->request->getGet('load_more') == 1) {
            $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));
            $limit  = max(1, (int) ($this->request->getGet('limit') ?? 20));
            $filters['offset'] = $offset;
            $filters['limit']  = $limit;

            $moreBills = $this->billModel->getBillsWithVendor($filters);
            return view('bills/list_rows', ['bills' => $moreBills]);
        }

        // Default initial load: 50 bills
        $filters['offset'] = 0;
        $filters['limit']  = 50;

        $data['bills'] = $this->billModel->getBillsWithVendor($filters);
        $data['vendors'] = $this->vendorModel->findAll();
        $data['filters'] = $filters;
        $data['title'] = 'Purchase Bills';

        $data['stats'] = [
            'total'                 => (int) ($totals['total_count'] ?? 0),
            'total_amount'          => (float) ($totals['total_amount'] ?? 0),
            'paid_amount'           => (float) ($totals['paid_amount'] ?? 0),
            'balance'               => (float) ($totals['total_balance'] ?? 0),
            'unpaid_amount'         => (float) ($totals['unpaid_amount'] ?? 0),
            'unpaid_count'          => (int) ($totals['unpaid_count'] ?? 0),
            'due_today_amount'      => (float) ($totals['due_today_amount'] ?? 0),
            'due_today_count'       => (int) ($totals['due_today_count'] ?? 0),
            'due_30_amount'         => (float) ($totals['due_30_amount'] ?? 0),
            'due_30_count'          => (int) ($totals['due_30_count'] ?? 0),
            'overdue_amount'        => (float) ($totals['overdue_amount'] ?? 0),
            'overdue_count'         => (int) ($totals['overdue_count'] ?? 0),
            'open_amount'           => (float) ($totals['open_amount'] ?? 0),
            'open_count'            => (int) ($totals['open_count'] ?? 0),
            'partially_paid_amount' => (float) ($totals['partially_paid_amount'] ?? 0),
            'partially_paid_count'  => (int) ($totals['partially_paid_count'] ?? 0),
            'paid_count'            => (int) ($totals['paid_count'] ?? 0),
        ];

        return view('bills/index', $data);
    }

    /**
     * Fetch Purchase Orders for a given vendor (used by bill form dropdown & prefill).
     */
    private function vendorPurchaseOrdersList(int $vendorId): array
    {
        if ($vendorId <= 0) {
            return [];
        }
        $db = \Config\Database::connect();
        if (!$db->tableExists('purchase_orders')) {
            return [];
        }
        return $db->table('purchase_orders')
            ->select('id, purchase_order_number, order_date, total_amount, status, converted_to_bill_id')
            ->where('vendor_id', $vendorId)
            ->whereNotIn('status', ['Void', 'Cancelled'])
            ->orderBy('order_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * AJAX endpoint: return Purchase Orders for a vendor as JSON.
     */
    public function getVendorPurchaseOrders($vendorId)
    {
        return $this->response->setJSON([
            'purchase_orders' => $this->vendorPurchaseOrdersList((int) $vendorId),
        ]);
    }

    public function create()
    {
        $data['vendors'] = $this->vendorModel->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['title'] = 'Create Purchase Bill';
        $data['bill'] = null;
        $data['is_clone'] = false;

        $poId = (int) ($this->request->getGet('po_id') ?? 0);
        if ($poId > 0) {
            $poModel = new \App\Models\PurchaseOrderModel();
            $po = $poModel->getOrderById($poId);
            if ($po) {
                $data['title'] = 'Convert PO #' . $po['purchase_order_number'] . ' to Bill';
                $data['bill'] = [
                    'vendor_id'        => $po['vendor_id'],
                    'bill_number'      => '',
                    'bill_date'        => date('Y-m-d'),
                    'due_date'         => date('Y-m-d', strtotime('+30 days')),
                    'reference_number' => $po['purchase_order_number'],
                    'is_inter_state'   => $po['is_inter_state'] ?? 0,
                    'discount_amount'  => $po['discount_amount'] ?? 0,
                    'discount_type'    => $po['discount_type'] ?? 'Amount',
                    'shipping_charge'  => $po['shipping_charge'] ?? 0,
                    'roundoff_amount'  => $po['roundoff_amount'] ?? 0,
                    'notes'            => $po['notes'] ?? '',
                    'terms'            => $po['terms'] ?? '',
                    'items'            => $po['items'] ?? [],
                ];
            }
        }

        $vendorId = (int) ($data['bill']['vendor_id'] ?? old('vendor_id') ?? 0);
        $data['vendor_pos'] = $this->vendorPurchaseOrdersList($vendorId);

        return view('bills/form', $data);
    }

    public function clone($id)
    {
        $src = $this->billModel->getBillById($id);
        if (!$src) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $src['bill_number']      = '';
        $src['reference_number'] = '';
        $src['bill_date']        = date('Y-m-d');
        $src['due_date']         = date('Y-m-d', strtotime('+30 days'));

        return view('bills/form', [
            'vendors'    => $this->vendorModel->findAll(),
            'products'   => $this->productModel->getProductsWithCategory(),
            'taxes'      => $this->taxModel->where('status', 'Active')->findAll(),
            'title'      => 'Clone Purchase Bill',
            'bill'       => $src,
            'is_clone'   => true,
            'vendor_pos' => $this->vendorPurchaseOrdersList((int) ($src['vendor_id'] ?? 0)),
        ]);
    }

    public function store()
    {
        $customNumber = trim((string) $this->request->getPost('bill_number'));
        if ($customNumber === '' || $customNumber === 'Auto-Generated') {
            return redirect()->back()->withInput()->with('error', 'Bill Number is required.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $billNumber = $customNumber;

        // Create bill
        $billData = [
            'vendor_id' => $this->request->getPost('vendor_id'),
            'bill_number' => $billNumber,
            'bill_date' => $this->request->getPost('bill_date'),
            'due_date' => $this->request->getPost('due_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'notes' => $this->request->getPost('notes'),
            'terms' => $this->request->getPost('terms'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'Amount',
            'shipping_charge' => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount' => $this->request->getPost('roundoff_amount') ?? 0,
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'status' => 'Draft',
            'created_by' => session('user_id'),
            'updated_by' => session('user_id'),
        ];

        if (!$this->billModel->insert($billData)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors', $this->billModel->errors());
        }

        $billId = $this->billModel->getInsertID();

        // Get vendor state for GST calculation
        $vendor = $this->vendorModel->select('vendors.*, addresses.state_id')
            ->join('addresses', 'addresses.owner_id = vendors.id AND addresses.owner_type = "vendor" AND addresses.address_type = "billing"', 'left')
            ->find($billData['vendor_id']);

        $vendorStateId = $vendor['state_id'] ?? null;

        // Get company state from settings
        $settingsModel = new \App\Models\SettingModel();
        $companyStateId = $settingsModel->getSetting('company_state') ?? null;

        $isInterState = ($vendorStateId != $companyStateId);

        // Save bill items
        $rawItems = $this->request->getPost('items') ?? [];
        $validItems = [];
        foreach ($rawItems as $item) {
            $hasProduct = !empty($item['product_id']);
            $hasDesc = !empty(trim($item['description'] ?? ''));
            $qty = floatval($item['quantity'] ?? 0);
            if (($hasProduct || $hasDesc) && $qty > 0) {
                $validItems[] = $item;
            }
        }

        if (empty($validItems)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Please add at least one valid item with quantity and rate.');
        }

        foreach ($validItems as $item) {
            $itemData = [
                'bill_id'        => $billId,
                'product_id'     => !empty($item['product_id']) ? $item['product_id'] : null,
                'description'    => $item['description'] ?? '',
                'hsn_code'       => trim((string) ($item['hsn_code'] ?? '')) ?: '5208',
                'quantity'       => floatval($item['quantity']),
                'rate'           => floatval($item['rate']),
                'tax_percentage' => floatval($item['tax_percentage'] ?? 0),
            ];

            // Apply GST rates
            $this->itemModel->applyGSTRates($itemData, $isInterState);
            $this->itemModel->insert($itemData);
        }

        // Calculate GST and totals
        $this->billModel->calculateGST($billId, $vendorStateId, $companyStateId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create bill.');
        }

        // Link matching Purchase Order as Billed when reference_number matches a vendor PO
        $refPoNumber = trim((string) ($billData['reference_number'] ?? ''));
        if ($refPoNumber !== '' && $db->tableExists('purchase_orders')) {
            $db->table('purchase_orders')
                ->where('vendor_id', (int) $billData['vendor_id'])
                ->where('purchase_order_number', $refPoNumber)
                ->update(['status' => 'Billed', 'converted_to_bill_id' => $billId]);
        }

        // --- ACCOUNTING LEDGER ---
        $finalBill = $this->billModel->find($billId);
        $billDate = $finalBill['bill_date'];
        $billRef = "Purchase Bill: " . $finalBill['bill_number'];

        // Calculations for Ledger
        $totalDiscount = 0;
        if ($finalBill['discount_type'] == 'Percentage') {
            $totalDiscount = ($finalBill['subtotal'] * $finalBill['discount_amount']) / 100;
        } else {
            $totalDiscount = $finalBill['discount_amount'];
        }

        // 1. Dr Cost of Purchase (Gross Subtotal)
        $this->accountingModel->postEntry('Cost of Purchase', $billDate, $finalBill['subtotal'], 0, $billRef, 'bill', $billId);

        // 2. Dr GST Receivable (Input Tax)
        if ($finalBill['tax_amount'] > 0) {
            $this->accountingModel->postEntry('GST Receivable', $billDate, $finalBill['tax_amount'], 0, "GST on $billRef", 'bill', $billId);
        }

        // 3. Dr Shipping / Other Expense (If any)
        if ($finalBill['shipping_charge'] > 0) {
            $this->accountingModel->postEntry('Cost of Purchase', $billDate, $finalBill['shipping_charge'], 0, "Shipping handling for $billRef", 'bill', $billId);
        }

        // 4. Cr Vendor Payment Discount (Income from discount)
        if ($totalDiscount > 0) {
            $this->accountingModel->postEntry('Vendor Payment Discount', $billDate, 0, $totalDiscount, "Discount Received on $billRef", 'bill', $billId);
        }

        // 5. Cr Accounts Payable (Total amount owed)
        $this->accountingModel->postEntry('Accounts Payable', $billDate, 0, $finalBill['total_amount'], $billRef, 'bill', $billId);
        // ------------------------

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('bills', (int) $billId, 'CREATE');
        }

        return redirect()->to('bills')->with('success', 'Bill created successfully.');
    }

    public function view($id)
    {
        $data['bill'] = $this->billModel->getBillById($id);

        if (!$data['bill']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Bill not found.']);
            }
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $data['title'] = 'Bill #' . $data['bill']['bill_number'];

        // AJAX request for side panel view
        if ($this->request->isAJAX() || $this->request->getGet('ajax') == 1) {
            return view('bills/detail_pane', $data);
        }

        $data['is_single'] = ($this->request->getGet('mode') === 'single');

        return view('bills/view', $data);
    }

    public function edit($id)
    {
        $data['bill'] = $this->billModel->getBillById($id);

        if (!$data['bill']) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $data['vendors'] = $this->vendorModel->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['title'] = 'Edit Bill #' . $data['bill']['bill_number'];
        $data['vendor_pos'] = $this->vendorPurchaseOrdersList((int) ($data['bill']['vendor_id'] ?? 0));

        return view('bills/form', $data);
    }

    public function update($id)
    {
        $bill = $this->billModel->find($id);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Update bill
        $billData = [
            'vendor_id' => $this->request->getPost('vendor_id'),
            'bill_number' => trim((string) $this->request->getPost('bill_number')) ?: $bill['bill_number'],
            'bill_date' => $this->request->getPost('bill_date'),
            'due_date' => $this->request->getPost('due_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'notes' => $this->request->getPost('notes'),
            'terms' => $this->request->getPost('terms'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'Amount',
            'shipping_charge' => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount' => $this->request->getPost('roundoff_amount') ?? 0,
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'updated_by' => session('user_id'),
        ];

        if (!$this->billModel->update($id, $billData)) {
            log_message('error', 'Bill update failed: ' . json_encode($this->billModel->errors()));
            return redirect()->back()->withInput()->with('errors', $this->billModel->errors());
        }

        // Delete existing items and re-add
        $this->itemModel->where('bill_id', $id)->delete();

        // Get vendor state
        $vendor = $this->vendorModel->select('vendors.*, addresses.state_id')
            ->join('addresses', 'addresses.owner_id = vendors.id AND addresses.owner_type = "vendor" AND addresses.address_type = "billing"', 'left')
            ->find($billData['vendor_id']);

        $vendorStateId = $vendor['state_id'] ?? null;
        $settingsModel = new \App\Models\SettingModel();
        $companyStateId = $settingsModel->getSetting('company_state') ?? null;
        $isInterState = ($vendorStateId != $companyStateId);

        // Re-save items
        $rawItems = $this->request->getPost('items') ?? [];
        $validItems = [];
        foreach ($rawItems as $item) {
            $hasProduct = !empty($item['product_id']);
            $hasDesc = !empty(trim($item['description'] ?? ''));
            $qty = floatval($item['quantity'] ?? 0);
            if (($hasProduct || $hasDesc) && $qty > 0) {
                $validItems[] = $item;
            }
        }

        if (empty($validItems)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Please add at least one valid item with quantity and rate.');
        }

        foreach ($validItems as $item) {
            $itemData = [
                'bill_id'        => $id,
                'product_id'     => !empty($item['product_id']) ? $item['product_id'] : null,
                'description'    => $item['description'] ?? '',
                'hsn_code'       => trim((string) ($item['hsn_code'] ?? '')) ?: '5208',
                'quantity'       => floatval($item['quantity']),
                'rate'           => floatval($item['rate']),
                'tax_percentage' => floatval($item['tax_percentage'] ?? 0),
            ];

            $this->itemModel->applyGSTRates($itemData, $isInterState);
            $this->itemModel->insert($itemData);
        }

        // Recalculate GST
        $this->billModel->calculateGST($id, $vendorStateId, $companyStateId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update bill.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('bills', (int) $id, 'UPDATE');
        }

        return redirect()->to('bills/view/' . $id)->with('success', 'Bill updated successfully.');
    }

    public function delete($id)
    {
        $bill = $this->billModel->find($id);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        // Void bill instead of deleting
        $this->billModel->update($id, [
            'status' => 'Void',
            'updated_by' => session('user_id')
        ]);

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('bills', (int) $id, 'VOID');
        }

        return redirect()->to('bills')->with('success', 'Bill voided successfully.');
    }

    public function markAsOpen($id)
    {
        $bill = $this->billModel->find($id);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        if ($bill['status'] !== 'Draft') {
            return redirect()->to('bills/view/' . $id)->with('error', 'Only Draft bills can be marked as Open.');
        }

        $this->billModel->update($id, [
            'status' => 'Open',
            'updated_by' => session('user_id')
        ]);

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('bills', (int) $id, 'UPDATE');
        }

        return redirect()->to('bills/view/' . $id)->with('success', 'Bill marked as Open successfully.');
    }

    public function recordPayment($billId)
    {
        $data['bill'] = $this->billModel->getBillById($billId);

        if (!$data['bill']) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $data['bank_accounts'] = $this->bankAccountModel->findAll();
        $data['title'] = 'Record Payment - Bill #' . $data['bill']['bill_number'];

        return view('bills/payment_form', $data);
    }

    public function storePayment($billId)
    {
        $bill = $this->billModel->find($billId);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $grossSettlement = (float) $this->request->getPost('gross_settlement');
        $amount = (float) $this->request->getPost('amount');
        $discount = (float) ($this->request->getPost('discount_amount') ?? 0);
        $mahimai = (float) ($this->request->getPost('mahimai_amount') ?? 0);
        $postal = (float) ($this->request->getPost('postal_charges') ?? 0);
        $bankAccountId = $this->request->getPost('bank_account_id');

        if (empty($bankAccountId)) {
            return redirect()->back()->withInput()->with('error', 'Bank account is mandatory.');
        }

        if ($grossSettlement > $bill['balance'] + 0.01) {
            return redirect()->back()->withInput()->with('error', 'Gross settlement cannot exceed bill balance.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Generate payment number
        $paymentNumber = $this->paymentModel->generatePaymentNumber();

        // Create payment
        $paymentData = [
            'bill_id' => $billId,
            'vendor_id' => $bill['vendor_id'],
            'payment_number' => $paymentNumber,
            'payment_date' => $this->request->getPost('payment_date'),
            'payment_mode' => $this->request->getPost('payment_mode'),
            'amount' => $amount,
            'discount_amount' => $discount,
            'mahimai_amount' => $mahimai,
            'postal_charges' => $postal,
            'reference_number' => $this->request->getPost('reference_number'),
            'bank_account_id' => $this->request->getPost('bank_account_id') ?: null,
            'bank_transaction_id' => $this->request->getPost('bank_transaction_id') ?: null,
            'notes' => $this->request->getPost('notes'),
            'created_by' => session('user_id'),
            'updated_by' => session('user_id'),
        ];

        $this->paymentModel->insert($paymentData);
        $paymentId = $this->paymentModel->getInsertID();

        // --- ACCOUNTING LEDGER ---
        $paymentDate = $paymentData['payment_date'];
        $paymentRef = "Vendor Payment: " . $paymentNumber . " (Ref: " . $bill['bill_number'] . ")";

        // 1. Dr Accounts Payable (Liability decreases)
        $this->accountingModel->postEntry('Accounts Payable', $paymentDate, $grossSettlement, 0, "Gross settlement for $paymentNumber", 'vendor_payment', $paymentId);

        // 2. Cr Bank/Cash (Asset decreases)
        $paymentAccount = ($paymentData['payment_mode'] == 'Cash') ? 'Cash' : 'Bank Account';
        $this->accountingModel->postEntry($paymentAccount, $paymentDate, 0, $amount, $paymentRef, 'vendor_payment', $paymentId);

        // 3. Cr Vendor Payment Discount (Income increases)
        if ($discount > 0) {
            $this->accountingModel->postEntry('Vendor Payment Discount', $paymentDate, 0, $discount, "Discount received on $paymentNumber", 'vendor_payment', $paymentId);
        }
        // ------------------------

        // Update bill paid amount with gross settlement
        $newPaidAmount = $bill['paid_amount'] + $grossSettlement;
        $this->billModel->update($billId, ['paid_amount' => $newPaidAmount]);

        // Update balance and status
        $this->billModel->updateBalance($billId);

        // Link to bank transaction if provided
        if ($paymentData['bank_transaction_id']) {
            $this->paymentModel->linkToBankTransaction($paymentId, $paymentData['bank_transaction_id']);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to record payment.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('vendor_payments', (int) $paymentId, 'CREATE');
            zoho_auto_sync('bills', (int) $billId, 'UPDATE');
        }

        return redirect()->to('bills/view/' . $billId)->with('success', 'Payment recorded successfully.');
    }

    /**
     * Print bill
     */
    /**
     * Ensure `bills.bill_copy` exists even if migration has not run yet.
     */
    private function ensureBillCopyColumn(): void
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('bill_copy', 'bills')) {
            $db->query("ALTER TABLE `bills` ADD COLUMN `bill_copy` VARCHAR(255) NULL AFTER `reference_number`");
        }
    }

    /**
     * Upload or replace a scanned bill copy (PDF / image) for an existing bill.
     */
    public function uploadCopy($id)
    {
        $this->ensureBillCopyColumn();
        $bill = $this->billModel->find($id);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $file = $this->request->getFile('bill_copy');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Please select a valid bill copy file.');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true)) {
            return redirect()->back()->with('error', 'Only PDF, JPG, PNG, or WEBP files are allowed.');
        }
        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->back()->with('error', 'File size must be 10 MB or less.');
        }

        $uploadDir = FCPATH . 'uploads/bills';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);
        \App\Libraries\ImageCompressor::compress($uploadDir . '/' . $newName);

        // Remove previous file if replacing
        if (!empty($bill['bill_copy'])) {
            $oldPath = FCPATH . ltrim((string) $bill['bill_copy'], '/');
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $this->billModel->update($id, [
            'bill_copy'  => 'uploads/bills/' . $newName,
            'updated_by' => session('user_id'),
        ]);

        return redirect()->back()->with('success', 'Bill copy uploaded successfully.');
    }

    /**
     * Remove the uploaded bill copy from an existing bill.
     */
    public function deleteCopy($id)
    {
        $this->ensureBillCopyColumn();
        $bill = $this->billModel->find($id);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        if (!empty($bill['bill_copy'])) {
            $oldPath = FCPATH . ltrim((string) $bill['bill_copy'], '/');
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
            $this->billModel->update($id, [
                'bill_copy'  => null,
                'updated_by' => session('user_id'),
            ]);
        }

        return redirect()->back()->with('success', 'Bill copy removed.');
    }

    public function print($id)
    {
        $bill = $this->billModel->getBillById($id);
        if (!$bill) {
            return redirect()->to('bills')->with('error', 'Bill not found.');
        }

        $settingsModel = new \App\Models\SettingModel();
        $data = [
            'bill'            => $bill,
            'settings'        => $settingsModel->getAllSettings(),
            'company_name'    => get_setting('app_name', 'RasiDev'),
            'company_address' => get_setting('company_address', ''),
            'company_gstin'   => get_setting('company_gstin', ''),
            'company_contact' => get_setting('company_phone', ''),
            'title'           => 'Purchase Bill - ' . $bill['bill_number']
        ];

        if ($this->request->getGet('format') !== 'html') {
            try {
                $html = view('bills/print', array_merge($data, ['auto_print' => false]));
                return \App\Libraries\PdfRenderer::streamDocument('bill', $html, 'Bill_' . $bill['bill_number']);
            } catch (\Throwable $e) {
                log_message('error', 'Bill PDF generation error: ' . $e->getMessage());
            }
        }

        return view('bills/print', $data);
    }

    /**
     * AJAX endpoint to check if a bill number already exists for a vendor.
     */
    public function checkDuplicate()
    {
        $vendorId   = (int) $this->request->getGet('vendor_id');
        $billNumber = trim((string) $this->request->getGet('bill_number'));
        $excludeId  = (int) ($this->request->getGet('exclude_id') ?? 0);

        if ($vendorId <= 0 || $billNumber === '') {
            return $this->response->setJSON(['has_duplicate' => false, 'bills' => []]);
        }

        $builder = $this->billModel
            ->select('bills.id, bills.bill_number, bills.reference_number, bills.bill_date, bills.due_date, bills.total_amount, bills.paid_amount, bills.balance, bills.status, vendors.name as vendor_name')
            ->join('vendors', 'vendors.id = bills.vendor_id', 'left')
            ->where('bills.vendor_id', $vendorId)
            ->groupStart()
                ->where('bills.bill_number', $billNumber)
                ->orWhere('bills.reference_number', $billNumber)
            ->groupEnd();

        if ($excludeId > 0) {
            $builder->where('bills.id !=', $excludeId);
        }

        $existingBills = $builder->orderBy('bills.bill_date', 'DESC')->findAll();

        return $this->response->setJSON([
            'has_duplicate' => count($existingBills) > 0,
            'count'         => count($existingBills),
            'bills'         => $existingBills,
        ]);
    }
}

