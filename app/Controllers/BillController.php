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

        // Overall aggregate stats
        $summaryBuilder = $this->billModel->select('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_amount, COALESCE(SUM(paid_amount), 0) as paid_amount, COALESCE(SUM(balance), 0) as balance');
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
            $summaryBuilder->where('status', $filters['status']);
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

        $stats = [
            'total'         => (int) ($totals['total_count'] ?? count($data['bills'])),
            'total_amount'  => (float) ($totals['total_amount'] ?? 0),
            'paid_amount'   => (float) ($totals['paid_amount'] ?? 0),
            'balance'       => (float) ($totals['balance'] ?? 0),
            'paid_count'    => 0,
            'open_count'    => 0,
            'overdue_count' => 0,
        ];
        foreach ($data['bills'] as $b) {
            $st = $b['status'] ?? '';
            if ($st === 'Paid') {
                $stats['paid_count']++;
            } elseif ($st === 'Overdue') {
                $stats['overdue_count']++;
            } elseif ($st === 'Open' || $st === 'Partially Paid') {
                $stats['open_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('bills/index', $data);
    }

    public function create()
    {
        $data['vendors'] = $this->vendorModel->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['title'] = 'Create Purchase Bill';
        $data['bill'] = null;

        return view('bills/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Generate bill number
        $billNumber = $this->billModel->generateBillNumber();

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
                'hsn_code'       => $item['hsn_code'] ?? '',
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
                'hsn_code'       => $item['hsn_code'] ?? '',
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
        $discount = (float) $this->request->getPost('discount_amount') ?? 0;
        $mahimai = (float) $this->request->getPost('mahimai_amount') ?? 0;
        $postal = (float) $this->request->getPost('postal_charges') ?? 0;

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

        return view('bills/print', $data);
    }
}
