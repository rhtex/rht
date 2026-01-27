<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\StateModel;
use App\Models\AddressModel;
use App\Services\ZohoBooksService;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $stateModel;
    protected $addressModel;
    protected $zohoService;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->stateModel = new StateModel();
        $this->addressModel = new AddressModel();
        $this->zohoService = new ZohoBooksService();
    }

    public function index()
    {
        $data['customers'] = $this->customerModel
            ->select('customers.*, addresses.address_line1, addresses.city, states.name as state_name')
            ->join('addresses', 'addresses.owner_id = customers.id AND addresses.owner_type = "customer" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->findAll();
        $data['title'] = 'Customer Management';
        return view('customers/index', $data);
    }

    public function create()
    {
        $data['states'] = $this->stateModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Add New Customer';
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

        // Push to Zoho
        $this->pushToZoho($customerId);

        return redirect()->to('customers')->with('success', 'Customer added successfully.');
    }

    public function view($id)
    {
        $data['customer'] = $this->customerModel->find($id);
        if (!$data['customer']) {
            return redirect()->to('customers')->with('error', 'Customer not found.');
        }

        $data['billing_address'] = $this->addressModel->getActiveAddress('customer', $id, 'billing');
        $data['shipping_address'] = $this->addressModel->getActiveAddress('customer', $id, 'shipping');
        if ($data['billing_address']) {
            $data['billing_state'] = $this->stateModel->find($data['billing_address']['state_id']);
        }
         if ($data['shipping_address']) {
            $data['shipping_state'] = $this->stateModel->find($data['shipping_address']['state_id']);
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

        $data['states'] = $this->stateModel->orderBy('name', 'ASC')->findAll();
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

        // Push to Zoho
        $this->pushToZoho($id);

        return redirect()->to('customers')->with('success', 'Customer updated successfully.');
    }

    public function delete($id)
    {
        $this->customerModel->delete($id);
        // Optionally delete addresses or keep them (audit trail)
        $this->addressModel->where('owner_type', 'customer')->where('owner_id', $id)->delete();
        return redirect()->to('customers')->with('success', 'Customer deleted successfully.');
    }

    private function pushToZoho($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) return;

        $billing = $this->addressModel->getActiveAddress('customer', $id, 'billing');
        $shipping = $this->addressModel->getActiveAddress('customer', $id, 'shipping');
        
        $billingState = $billing ? $this->stateModel->find($billing['state_id']) : null;
        $shippingState = $shipping ? $this->stateModel->find($shipping['state_id']) : null;

        $data = [
            'contact_name' => $customer['name'],
            'company_name' => $customer['name'], // Use customer name as company name
            'contact_type' => 'customer',
            'email'        => $customer['email'],
            'phone'        => $customer['phone'],
            'mobile'       => $customer['whatsapp_number'] ?? $customer['phone'],
            'website'      => $customer['website'],
            'gst_no'       => $customer['gstin'],
            'pan'          => $customer['pan_number'],
            'billing_address' => [
                'address' => $billing['address_line1'] ?? '',
                'street2' => $billing['address_line2'] ?? '',
                'city'    => $billing['city'] ?? '',
                'state'   => $billingState['name'] ?? '',
                'zip'     => $billing['pincode'] ?? '',
                'country' => 'India'
            ],
            'shipping_address' => [
                'address' => $shipping['address_line1'] ?? '',
                'street2' => $shipping['address_line2'] ?? '',
                'city'    => $shipping['city'] ?? '',
                'state'   => $shippingState['name'] ?? '',
                'zip'     => $shipping['pincode'] ?? '',
                'country' => 'India'
            ]
        ];

        $response = $this->zohoService->pushContact($data, $customer['zoho_contact_id']);
        
        if ($response['success']) {
            $zohoId = $response['data']['contact']['contact_id'];
            $this->customerModel->update($id, [
                'zoho_contact_id' => $zohoId,
                'zoho_sync_at'    => date('Y-m-d H:i:s')
            ]);
        } else {
            log_message('error', 'Zoho Push Error for Customer ' . $id . ': ' . $response['message']);
        }
    }

    public function syncZoho()
    {
        $page = 1;
        $hasMore = true;
        $syncedCount = 0;

        while ($hasMore) {
            $response = $this->zohoService->getContacts('customer', $page);
            
            if (!$response['success']) {
                if ($syncedCount > 0) break; // If we already synced some, don't show error (or log it)
                return redirect()->to('customers')->with('error', 'Failed to fetch from Zoho: ' . $response['message']);
            }

            $contacts = $response['data']['contacts'] ?? [];
            if (empty($contacts)) break;

            foreach ($contacts as $contact) {
                // Check if already exists by zoho_id
                $existing = $this->customerModel->where('zoho_contact_id', $contact['contact_id'])->first();
                
                $customerData = [
                    'zoho_contact_id' => $contact['contact_id'],
                    'name'            => $contact['company_name'] ?: $contact['contact_name'],
                    'email'           => $contact['email'],
                    'phone'           => $contact['phone'],
                    'website'         => $contact['website'],
                    'zoho_sync_at'    => date('Y-m-d H:i:s'),
                    'status'          => 'active'
                ];

                if ($existing) {
                    $this->customerModel->update($existing['id'], $customerData);
                    $customerId = $existing['id'];
                } else {
                    // Also check by email if no zoho_id link yet
                    $byEmail = $this->customerModel->where('email', $contact['email'])->first();
                    if ($byEmail && !empty($contact['email'])) {
                        $this->customerModel->update($byEmail['id'], $customerData);
                        $customerId = $byEmail['id'];
                    } else {
                        $this->customerModel->insert($customerData);
                        $customerId = $this->customerModel->getInsertID();
                    }
                }

                // Sync Address
                if (isset($contact['billing_address'])) {
                    $this->syncAddress($customerId, 'customer', 'billing', $contact['billing_address']);
                }
                if (isset($contact['shipping_address'])) {
                    $this->syncAddress($customerId, 'customer', 'shipping', $contact['shipping_address']);
                }

                $syncedCount++;
            }

            // Check if there are more pages
            $hasMore = $response['data']['page_context']['has_more_page'] ?? false;
            $page++;
        }

        return redirect()->to('customers')->with('success', "Successfully synced $syncedCount customers from Zoho.");
    }

    private function syncAddress($ownerId, $ownerType, $addressType, $zohoAddr)
    {
        if (empty($zohoAddr['address']) && empty($zohoAddr['city'])) return;

        $stateName = $zohoAddr['state'] ?? '';
        $state = $this->stateModel->where('name', $stateName)->first();
        
        $newData = [
            'owner_type'    => $ownerType,
            'owner_id'      => $ownerId,
            'address_type'  => $addressType,
            'address_line1' => $zohoAddr['address'] ?? '',
            'address_line2' => $zohoAddr['street2'] ?? '',
            'city'          => $zohoAddr['city'] ?? '',
            'pincode'       => $zohoAddr['zip'] ?? '',
            'state_id'      => $state['id'] ?? null,
            'country_id'    => 1,
            'is_active'     => 1
        ];

        $existing = $this->addressModel->getActiveAddress($ownerType, $ownerId, $addressType);
        
        if ($existing) {
            $isChanged = false;
            foreach (['address_line1', 'city', 'pincode', 'state_id'] as $field) {
                if (($existing[$field] ?? '') != ($newData[$field] ?? '')) {
                    $isChanged = true;
                    break;
                }
            }

            if ($isChanged) {
                $this->addressModel->deactivateOthers($ownerType, $ownerId, $addressType);
                $this->addressModel->insert($newData);
            }
        } else {
            $this->addressModel->insert($newData);
        }
    }

    /**
     * Save addresses from form data
     */
    private function saveAddresses($ownerType, $ownerId)
    {
        // Save Billing Address
        $billingData = [
            'owner_type'    => $ownerType,
            'owner_id'      => $ownerId,
            'address_type'  => 'billing',
            'address_line1' => $this->request->getPost('billing_address_line1'),
            'address_line2' => $this->request->getPost('billing_address_line2'),
            'city'          => $this->request->getPost('billing_city'),
            'state_id'      => $this->request->getPost('billing_state_id'),
            'pincode'       => $this->request->getPost('billing_pincode'),
            'country_id'    => 1, // India
            'is_active'     => 1
        ];

        $existingBilling = $this->addressModel->getActiveAddress($ownerType, $ownerId, 'billing');
        
        if ($existingBilling) {
            $isChanged = false;
            foreach (['address_line1', 'address_line2', 'city', 'state_id', 'pincode'] as $field) {
                if (($existingBilling[$field] ?? '') != ($billingData[$field] ?? '')) {
                    $isChanged = true;
                    break;
                }
            }

            if ($isChanged) {
                $this->addressModel->deactivateOthers($ownerType, $ownerId, 'billing');
                $this->addressModel->insert($billingData);
            }
        } else {
            $this->addressModel->insert($billingData);
        }

        // Save Shipping Address (if different from billing)
        if ($this->request->getPost('shipping_address_line1')) {
            $shippingData = [
                'owner_type'    => $ownerType,
                'owner_id'      => $ownerId,
                'address_type'  => 'shipping',
                'address_line1' => $this->request->getPost('shipping_address_line1'),
                'address_line2' => $this->request->getPost('shipping_address_line2'),
                'city'          => $this->request->getPost('shipping_city'),
                'state_id'      => $this->request->getPost('shipping_state_id'),
                'pincode'       => $this->request->getPost('shipping_pincode'),
                'country_id'    => 1,
                'is_active'     => 1
            ];

            $existingShipping = $this->addressModel->getActiveAddress($ownerType, $ownerId, 'shipping');
            
            if ($existingShipping) {
                $isChanged = false;
                foreach (['address_line1', 'address_line2', 'city', 'state_id', 'pincode'] as $field) {
                    if (($existingShipping[$field] ?? '') != ($shippingData[$field] ?? '')) {
                        $isChanged = true;
                        break;
                    }
                }

                if ($isChanged) {
                    $this->addressModel->deactivateOthers($ownerType, $ownerId, 'shipping');
                    $this->addressModel->insert($shippingData);
                }
            } else {
                $this->addressModel->insert($shippingData);
            }
        }
    }
}
