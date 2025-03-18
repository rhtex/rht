<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\AddressModel;
use App\Models\AgentModel;  // Add AgentModel
use App\Models\StateModel;
use CodeIgniter\Controller;

class CustomersController extends BaseController
{
    // List Customers
    public function index()
    {
        $customerModel = new CustomerModel();
        $data['customers'] = $customerModel->findAll();
        $data['pageTitle'] = 'Customer List';  // Set the page title

        return view('customers/list_customers', $data);
    }

    // Create Customer
    public function create()
    {
        // Fetch agents from AgentModel (assuming you have an agents table)
        $agentModel = new AgentModel();
        $data['agents'] = $agentModel->findAll();  // Retrieve all agents from the database
        $stateModel = new StateModel();
        $data['states'] = $stateModel->getAllStates();
        $data['pageTitle'] = 'Create Customer';  // Set the page title
        return view('customers/add_customer', $data);
    }

    // View Single Customer
    public function view($id)
    {
        $customerModel = new CustomerModel();
        $data['customer'] = $customerModel->find($id);

        if (!$data['customer']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Customer with ID $id not found.");
        }

        $data['pageTitle'] = 'Customer Details';  // Set the page title

        return view('customers/view_customer', $data);
    }

    // Edit Customer Form
    public function edit($id)
    {
        $customerModel = new CustomerModel();
        $data['customer'] = $customerModel->find($id);

        if (!$data['customer']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Customer with ID $id not found.");
        }

        $data['pageTitle'] = 'Edit Customer';  // Set the page title

        return view('customers/edit_customer', $data);
    }

    // Update Customer
    public function update($id)
    {
        $customerModel = new CustomerModel();

        $data = [
            'display_name' => $this->request->getPost('display_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            // Add other fields to be updated
        ];

        $customerModel->update($id, $data);

        return redirect()->to('/customers')->with('success', 'Customer updated successfully!');
    }

    // Delete Customer
    public function delete($id)
    {
        $customerModel = new CustomerModel();
        $customerModel->delete($id);

        return redirect()->to('/customers')->with('success', 'Customer deleted successfully!');
    }

    // Store New Customer
    public function store()
    {
        // Load models
        $customerModel = new CustomerModel();
        $addressModel = new AddressModel();

        // Get form data
        $data = $this->request->getPost();
        $currentUser = session()->get('user_id'); // Adjust based on your authentication system

        // Prepare Customer Data
        $customerData = [
            'type' => $data['type'],
            'salutation' => $data['salutation'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'company_name' => $data['company_name'],
            'display_name' => $data['display_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'remarks' => $data['remarks'],
            'gst_treatment' => $data['gst_treatment'],
            'place_of_supply' => $data['place_of_supply'],
            'pan' => $data['pan'],
            'tax_preference' => $data['tax_preference'],
            'currency' => $data['currency'] ?? 'inr',
            'opening_balance' => $data['opening_balance'],
            'payment_terms' => $data['payment_terms'],
            'enable_portal' => $data['enable_portal'] ?? 1,
            'portal_language' => $data['portal_language'],
            'created_by' => $currentUser,
            'updated_by' => $currentUser,
        ];

        // Insert customer data
        $customerId = $customerModel->insert($customerData);

        // Prepare Billing Address Data
        $billingAddress = [
            'customer_id' => $customerId,
            'address_type' => 'billing',
            'attention' => $data['billing_attention'],
            'country' => $data['billing_country'],
            'street1' => $data['billing_street1'],
            'street2' => $data['billing_street2'],
            'city' => $data['billing_city'],
            'state' => $data['billing_state'],
            'pin_code' => $data['billing_pin_code'],
            'phone' => $data['billing_phone'],
            'fax' => $data['billing_fax'],
            'created_by' => $currentUser,
            'updated_by' => $currentUser,
        ];

        // Insert billing address data
        $addressModel->insert($billingAddress);

        // If shipping address is not to be copied, insert shipping address
        if (!isset($data['copy_billing']) || !$data['copy_billing']) {
            $shippingAddress = [
                'customer_id' => $customerId,
                'address_type' => 'shipping',
                'attention' => $data['shipping_attention'],
                'country' => $data['shipping_country'],
                'street1' => $data['shipping_street1'],
                'street2' => $data['shipping_street2'],
                'city' => $data['shipping_city'],
                'state' => $data['shipping_state'],
                'pin_code' => $data['shipping_pin_code'],
                'phone' => $data['shipping_phone'],
                'fax' => $data['shipping_fax'],
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ];

            // Insert shipping address data
            $addressModel->insert($shippingAddress);
        }

        // Redirect to the customer list page with a success message
        return redirect()->to('/customers')->with('success', 'Customer added successfully!');
    }
}
