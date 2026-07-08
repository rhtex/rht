<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\AgentModel;
use App\Models\StateModel;
use App\Models\AddressModel;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $stateModel;
    protected $countryModel;
    protected $addressModel;
    protected $agentModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->stateModel = new StateModel();
        $this->countryModel = new \App\Models\CountryModel();
        $this->addressModel = new AddressModel();
        $this->agentModel = new AgentModel();
    }

    public function index()
    {
        $limit = 25;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $filters = [
            'search' => $this->request->getGet('search'),
            'agent_id' => $this->request->getGet('agent_id'),
            'status' => $this->request->getGet('status'),
            'sort_by' => $this->request->getGet('sort_by'),
            'sort_order' => $this->request->getGet('sort_order'),
        ];

        if ($this->request->isAJAX()) {
            $customers = $this->customerModel->getCustomersWithFilters($filters, $limit, $offset);
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $customers,
                'has_more' => count($customers) == $limit
            ]);
        }

        $data['customers'] = $this->customerModel->getCustomersWithFilters($filters, $limit, 0);
        $data['agents'] = $this->agentModel->findAll();
        $data['title'] = 'Customer Management';
        $data['filters'] = $filters;
        $data['has_more'] = count($data['customers']) == $limit;

        return view('customers/index', $data);
    }

    public function create()
    {
        $data['countries'] = $this->countryModel->orderBy('CASE WHEN name = "India" THEN 0 ELSE 1 END', 'ASC', false)->orderBy('name', 'ASC')->findAll();
        // Just fetch empty states or states for default country (India) if we want.
        // For simplicity, let's pass all states or let JS handle it.
        // Actually, better to pass all states if not too many, OR handle via AJAX.
        // Existing code passed all states. Let's keep it but ideally we filter.
        // If we want dynamic, we usually just pass empty array or default country states.
        // Let's stick to existing pattern but add countries.
        $data['states'] = $this->stateModel->orderBy('name', 'ASC')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['title'] = 'Add New Customer';
        $data['billing_address'] = [];
        $data['shipping_address'] = [];
        return view('customers/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Save Customer
        $customerData = $this->request->getPost();
        if (!$this->customerModel->insert($customerData)) {
            return redirect()->back()->withInput()->with('errors', $this->customerModel->errors());
        }
        $customerId = $this->customerModel->getInsertID();

        // 2. Save Addresses
        $this->saveAddresses('customer', $customerId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save customer data.');
        }


        return redirect()->to('customers')->with('success', 'Customer added successfully.');
    }

    public function view($id)
    {
        $data['customer'] = $this->customerModel->find($id);
        if (!$data['customer']) {
            return redirect()->to('customers')->with('error', 'Customer not found.');
        }

        $data['agent'] = null;
        if (!empty($data['customer']['agent_id'])) {
            $data['agent'] = $this->agentModel->find($data['customer']['agent_id']);
        }

        $data['billing_address'] = $this->addressModel->getActiveAddress('customer', $id, 'billing');
        $data['shipping_address'] = $this->addressModel->getActiveAddress('customer', $id, 'shipping');

        $data['billing_country'] = null;
        $data['billing_state'] = null;
        $data['shipping_country'] = null;
        $data['shipping_state'] = null;

        if ($data['billing_address']) {
            $data['billing_state'] = $this->stateModel->find($data['billing_address']['state_id']);
            $data['billing_country'] = $this->countryModel->find($data['billing_address']['country_id']);
        }
        if ($data['shipping_address']) {
            $data['shipping_state'] = $this->stateModel->find($data['shipping_address']['state_id']);
            $data['shipping_country'] = $this->countryModel->find($data['shipping_address']['country_id']);
        }

        // Fetch Transactions
        $invoiceModel = new \App\Models\InvoiceModel();
        $data['invoices'] = $invoiceModel->where('customer_id', $id)->orderBy('invoice_date', 'DESC')->findAll();

        $quotationModel = new \App\Models\QuotationModel();
        $data['quotations'] = $quotationModel->where('customer_id', $id)->orderBy('quotation_date', 'DESC')->findAll();

        $salesOrderModel = new \App\Models\SalesOrderModel();
        $data['sales_orders'] = $salesOrderModel->where('customer_id', $id)->orderBy('order_date', 'DESC')->findAll();

        $paymentModel = new \App\Models\InvoicePaymentModel();
        // Payments are linked to invoices, so we need to join or fetch via invoice IDs.
        // Or better, fetch payments where invoice.customer_id = $id
        $data['payments'] = $paymentModel->select('invoice_payments.*, invoices.invoice_number')
            ->join('invoices', 'invoices.id = invoice_payments.invoice_id')
            ->where('invoices.customer_id', $id)
            ->orderBy('payment_date', 'DESC')
            ->findAll();

        $returnModel = new \App\Models\SalesReturnModel();
        $data['returns'] = $returnModel->getReturnsByCustomer($id);

        // Calculate Pending Balance (matching Model logic)
        $vendorWithBalance = $this->customerModel->getCustomersWithFilters(['search' => $data['customer']['name']], 1, 0);
        $data['pending_balance'] = !empty($vendorWithBalance) ? $vendorWithBalance[0]['pending_balance'] : 0;

        $data['title'] = $data['customer']['name'];
        return view('customers/view', $data);
    }

    public function edit($id)
    {
        $data['customer'] = $this->customerModel->find($id);
        if (!$data['customer']) {
            return redirect()->to('customers')->with('error', 'Customer not found.');
        }

        $data['billing_address'] = $this->addressModel->getActiveAddress('customer', $id, 'billing');
        $data['shipping_address'] = $this->addressModel->getActiveAddress('customer', $id, 'shipping');

        $data['countries'] = $this->countryModel->orderBy('CASE WHEN name = "India" THEN 0 ELSE 1 END', 'ASC', false)->orderBy('name', 'ASC')->findAll();
        $data['states'] = $this->stateModel->orderBy('name', 'ASC')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['title'] = 'Edit Customer';
        return view('customers/form', $data);
    }

    public function update($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Update Customer
        $customerData = $this->request->getPost();
        if (!$this->customerModel->update($id, $customerData)) {
            return redirect()->back()->withInput()->with('errors', $this->customerModel->errors());
        }

        // 2. Update Addresses
        $this->saveAddresses('customer', $id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update customer data.');
        }


        return redirect()->to('customers')->with('success', 'Customer updated successfully.');
    }

    public function statement($id)
    {
        $data['customer'] = $this->customerModel->find($id);
        if (!$data['customer']) {
            return redirect()->to('customers')->with('error', 'Customer not found.');
        }

        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        // Fetch Invoices
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoicesBuilder = $invoiceModel->where('customer_id', $id);
        if ($dateFrom)
            $invoicesBuilder->where('invoice_date >=', $dateFrom);
        if ($dateTo)
            $invoicesBuilder->where('invoice_date <=', $dateTo);
        $invoices = $invoicesBuilder->orderBy('invoice_date', 'ASC')->findAll();

        // Fetch Payments
        $paymentModel = new \App\Models\InvoicePaymentModel();
        $paymentsBuilder = $paymentModel->select('invoice_payments.*, invoices.invoice_number')
            ->join('invoices', 'invoices.id = invoice_payments.invoice_id')
            ->where('invoices.customer_id', $id);
        if ($dateFrom)
            $paymentsBuilder->where('payment_date >=', $dateFrom);
        if ($dateTo)
            $paymentsBuilder->where('payment_date <=', $dateTo);
        $payments = $paymentsBuilder->orderBy('payment_date', 'ASC')->findAll();

        // Fetch Returns
        $returnModel = new \App\Models\SalesReturnModel();
        $returnsBuilder = $returnModel->where('customer_id', $id);
        if ($dateFrom)
            $returnsBuilder->where('return_date >=', $dateFrom);
        if ($dateTo)
            $returnsBuilder->where('return_date <=', $dateTo);
        $returns = $returnsBuilder->orderBy('return_date', 'ASC')->findAll();

        // Prepare Transactions
        $transactions = [];

        // Initial Balance (if no date filter, use opening balance. if date filter, need to calculate balance before dateFrom)
        $openingBalance = $data['customer']['opening_balance'];
        $openingType = $data['customer']['balance_type']; // Dr or Cr

        if ($dateFrom) {
            // Calculate balance before dateFrom
            $prevInvoices = $invoiceModel->where('customer_id', $id)->where('invoice_date <', $dateFrom)->selectSum('total_amount')->first();
            $prevPayments = $paymentModel->join('invoices', 'invoices.id = invoice_payments.invoice_id')
                ->where('invoices.customer_id', $id)->where('payment_date <', $dateFrom)->selectSum('amount')->first();
            $prevReturns = $returnModel->where('customer_id', $id)->where('return_date <', $dateFrom)->selectSum('total_amount')->first();

            $prevDebit = ($openingType == 'Dr' ? $openingBalance : 0) + ($prevInvoices['total_amount'] ?? 0);
            $prevCredit = ($openingType == 'Cr' ? $openingBalance : 0) + ($prevPayments['amount'] ?? 0) + ($prevReturns['total_amount'] ?? 0);

            if ($prevDebit >= $prevCredit) {
                $openingBalance = $prevDebit - $prevCredit;
                $openingType = 'Dr';
            } else {
                $openingBalance = $prevCredit - $prevDebit;
                $openingType = 'Cr';
            }
            $data['opening_balance_desc'] = 'Balance as on ' . date('d M, Y', strtotime($dateFrom));
        } else {
            $data['opening_balance_desc'] = 'Opening Balance';
        }

        $data['opening_balance'] = $openingBalance;
        $data['opening_type'] = $openingType;

        // Invoices (Debit)
        foreach ($invoices as $inv) {
            $transactions[] = [
                'date' => $inv['invoice_date'],
                'type' => 'Invoice',
                'reference' => $inv['invoice_number'],
                'debit' => $inv['total_amount'],
                'credit' => 0,
                'description' => 'Invoice #' . $inv['invoice_number']
            ];
        }

        // Payments (Credit)
        foreach ($payments as $pay) {
            $transactions[] = [
                'date' => $pay['payment_date'],
                'type' => 'Payment',
                'reference' => $pay['payment_number'] ?: 'REC-' . $pay['id'],
                'debit' => 0,
                'credit' => $pay['amount'],
                'description' => 'Payment for Invoice #' . $pay['invoice_number'] . ' (' . $pay['payment_mode'] . ')'
            ];
        }

        // Returns (Credit)
        foreach ($returns as $ret) {
            $transactions[] = [
                'date' => $ret['return_date'],
                'type' => 'Return',
                'reference' => $ret['return_number'],
                'debit' => 0,
                'credit' => $ret['total_amount'],
                'description' => 'Sales Return #' . $ret['return_number']
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $data['transactions'] = $transactions;
        $data['date_from'] = $dateFrom;
        $data['date_to'] = $dateTo;

        // Calculate Totals for export
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($transactions as $t) {
            $totalDebit += $t['debit'];
            $totalCredit += $t['credit'];
        }
        $finalBalance = ($openingType == 'Dr' ? $openingBalance : -$openingBalance) + $totalDebit - $totalCredit;

        $data['totalDebit'] = $totalDebit;
        $data['totalCredit'] = $totalCredit;
        $data['finalBalance'] = $finalBalance;

        $data['billing_address'] = $this->addressModel->getActiveAddress('customer', $id, 'billing');
        $data['billing_state'] = $data['billing_address'] ? $this->stateModel->find($data['billing_address']['state_id']) : null;

        $format = $this->request->getGet('format');

        if ($format == 'pdf') {
            $data['is_pdf'] = true;
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml(view('customers/statement', $data));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("Statement_{$data['customer']['name']}_" . date('Ymd') . ".pdf", ["Attachment" => true]);
            exit();
        }

        if ($format == 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Statement_' . str_replace(' ', '_', $data['customer']['name']) . '_' . date('Ymd') . '.csv');
            $output = fopen('php://output', 'w');

            // Header Info
            fputcsv($output, ['RASI DESIGNS']);
            fputcsv($output, ['Statement of Account']);
            fputcsv($output, ['Customer:', $data['customer']['name']]);
            fputcsv($output, ['Period:', ($dateFrom ?: 'Beginning') . ' - ' . ($dateTo ?: date('Y-m-d'))]);
            fputcsv($output, ['Balance:', number_format(abs($finalBalance), 2, '.', '') . ' ' . ($finalBalance >= 0 ? 'Dr' : 'Cr')]);
            fputcsv($output, []);

            // Table Header
            fputcsv($output, ['Date', 'Type', 'Reference', 'Description', 'Debit (Dr)', 'Credit (Cr)', 'Running Balance']);

            // Opening Balance
            fputcsv($output, [
                $dateFrom ?: '---',
                'Opening Balance',
                '',
                $data['opening_balance_desc'],
                $openingType == 'Dr' ? number_format($openingBalance, 2, '.', '') : '0.00',
                $openingType == 'Cr' ? number_format($openingBalance, 2, '.', '') : '0.00',
                number_format($openingBalance, 2, '.', '') . ' ' . $openingType
            ]);

            $runningBalance = ($openingType == 'Dr' ? $openingBalance : -$openingBalance);
            foreach ($transactions as $t) {
                $runningBalance += ($t['debit'] - $t['credit']);
                $balanceType = $runningBalance >= 0 ? 'Dr' : 'Cr';
                fputcsv($output, [
                    $t['date'],
                    $t['type'],
                    $t['reference'],
                    $t['description'],
                    $t['debit'] > 0 ? number_format($t['debit'], 2, '.', '') : '0.00',
                    $t['credit'] > 0 ? number_format($t['credit'], 2, '.', '') : '0.00',
                    number_format(abs($runningBalance), 2, '.', '') . ' ' . $balanceType
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['', '', '', 'TOTALS', number_format($totalDebit, 2, '.', ''), number_format($totalCredit, 2, '.', ''), '']);
            fputcsv($output, ['', '', '', 'FINAL BALANCE', '', '', number_format(abs($finalBalance), 2, '.', '') . ' ' . ($finalBalance >= 0 ? 'Dr' : 'Cr')]);

            fclose($output);
            exit();
        }

        return view('customers/statement', $data);
    }

    public function delete($id)
    {
        $this->customerModel->delete($id);
        // Optionally delete addresses or keep them (audit trail)
        $this->addressModel->where('owner_type', 'customer')->where('owner_id', $id)->delete();
        return redirect()->to('customers')->with('success', 'Customer deleted successfully.');
    }

    public function getDetails($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Customer not found'])->setStatusCode(404);
        }

        $billingAddress = $this->addressModel->getAddressWithNames('customer', $id, 'billing');
        $shippingAddress = $this->addressModel->getAddressWithNames('customer', $id, 'shipping');

        $billingText = $billingAddress ?
            "{$billingAddress['address_line1']}\n" .
            ($billingAddress['address_line2'] ? "{$billingAddress['address_line2']}\n" : "") .
            "{$billingAddress['city']}, {$billingAddress['state_name']}, {$billingAddress['country_name']} - {$billingAddress['pincode']}" : "N/A";

        $shippingText = $shippingAddress ?
            "{$shippingAddress['address_line1']}\n" .
            ($shippingAddress['address_line2'] ? "{$shippingAddress['address_line2']}\n" : "") .
            "{$shippingAddress['city']}, {$shippingAddress['state_name']}, {$shippingAddress['country_name']} - {$shippingAddress['pincode']}" : "N/A";

        $settingsModel = new \App\Models\SettingModel();
        $companyStateId = $settingsModel->getSetting('company_state') ?? null;

        $stateId = $billingAddress['state_id'] ?? null;

        $agentCommission = 0;
        if ($customer['agent_id']) {
            $agentModel = new \App\Models\AgentModel();
            $agent = $agentModel->find($customer['agent_id']);
            $agentCommission = $agent['commission_percentage'] ?? 0;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'state_id' => $stateId,
            'is_inter_state' => ($stateId != $companyStateId),
            'billing_address' => $billingText,
            'shipping_address' => $shippingText,
            'agent_id' => $customer['agent_id'] ?? null,
            'agent_commission' => $agentCommission,
            'credit_period_days' => $customer['credit_period_days'] ?? 0,
            'phone' => $customer['phone'] ?? '',
            'whatsapp_number' => $customer['whatsapp_number'] ?? '',
            'gst_type' => $customer['gst_type'] ?? 'Unregistered',
            'gstin' => $customer['gstin'] ?? 'N/A',
            'billing_state' => $billingAddress['state_name'] ?? '',
            'billing_country' => $billingAddress['country_name'] ?? '',
            'shipping_state' => $shippingAddress['state_name'] ?? '',
            'shipping_country' => $shippingAddress['country_name'] ?? '',
        ]);
    }

    /**
     * Save addresses from form data
     */
    private function saveAddresses($ownerType, $ownerId)
    {
        $addressesToProcess = [
            'billing' => [
                'address_line1' => $this->request->getPost('billing_address_line1'),
                'address_line2' => $this->request->getPost('billing_address_line2'),
                'city' => $this->request->getPost('billing_city'),
                'state_id' => $this->request->getPost('billing_state_id'),
                'pincode' => $this->request->getPost('billing_pincode'),
                'country_id' => $this->request->getPost('billing_country_id') ?: 1,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'address_type' => 'billing',
                'is_active' => 1
            ],
            'shipping' => [
                'address_line1' => $this->request->getPost('shipping_address_line1'),
                'address_line2' => $this->request->getPost('shipping_address_line2'),
                'city' => $this->request->getPost('shipping_city'),
                'state_id' => $this->request->getPost('shipping_state_id'),
                'pincode' => $this->request->getPost('shipping_pincode'),
                'country_id' => $this->request->getPost('shipping_country_id') ?: 1,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'address_type' => 'shipping',
                'is_active' => 1
            ]
        ];

        foreach ($addressesToProcess as $type => $data) {
            // Skip shipping if line1 is empty
            if ($type === 'shipping' && empty($data['address_line1'])) {
                continue;
            }

            $existing = $this->addressModel->getActiveAddress($ownerType, $ownerId, $type);

            $isChanged = true;
            if ($existing) {
                $isChanged = false;
                foreach (['address_line1', 'address_line2', 'city', 'state_id', 'pincode', 'country_id'] as $field) {
                    if (($existing[$field] ?? '') != ($data[$field] ?? '')) {
                        $isChanged = true;
                        break;
                    }
                }
            }

            if ($isChanged) {
                log_message('debug', "Address Update - Change detected for $type address of $ownerType $ownerId");

                // Validate before proceeding
                if (!$this->addressModel->validate($data)) {
                    log_message('error', "Address Update - Validation failed for $type: " . json_encode($this->addressModel->errors()));
                    continue; // Or throw an exception if we want to roll back the whole transaction
                }

                $this->addressModel->deactivateOthers($ownerType, $ownerId, $type);
                $this->addressModel->insert($data);
            }
        }
    }
}
