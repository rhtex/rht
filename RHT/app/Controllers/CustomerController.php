<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\CustomerAddressModel;
use CodeIgniter\Controller;

class CustomerController extends Controller
{
    protected $zohoBooksService;
    public function __construct()
    {
        helper('state');
    }
    public function index()
    {
        $model = new CustomerModel();
        $data['customers'] = $model->findAll();
        $data['pageTitle'] = 'Customer List';  
        return view('/customers/list', $data);
    }

    public function create()
    {
        helper(['form']);

        $customerModel = new CustomerModel();
        $addressModel = new CustomerAddressModel();
        $ZohoBooksController = new ZohoBooksController();

        if ($this->request->getMethod() === 'POST' && $this->validate([
            'company_name' => 'required',
            'contact_name' => 'required',
            'billing_address1' => 'required',
            'billing_city' => 'required',
            'billing_state' => 'required',
            'billing_country' => 'required',
            // Add other validation rules as needed
        ])) {
            $customerData = [
                'company_name' => $this->request->getPost('company_name'),
                'contact_name' => $this->request->getPost('contact_name'),
                'credit_limit' => $this->request->getPost('credit_limit'),
                'tax_type' => $this->request->getPost('tax_type'),
                'gst_no' => $this->request->getPost('gst_no'),
                'preferred_transport' => $this->request->getPost('preferred_transport'),
                'contact_no' => $this->request->getPost('contact_no'),
                'email' => $this->request->getPost('email'),
                'payment_terms' => $this->request->getPost('payment_terms'),
                'created_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s'),
                'customer_sub_type' => $this->request->getPost('gst_no') != '' ? 'business' : 'individual'
            ];

            $customerModel->insert($customerData);
            $customerId = $customerModel->insertID();

            $billingAddress = [
                'customer_id' => $customerId,
                'address_type' => 'billing',
                'address1' => $this->request->getPost('billing_address1'),
                'address2' => $this->request->getPost('billing_address2'),
                'city' => $this->request->getPost('billing_city'),
                'state' => $this->request->getPost('billing_state'),
                'country' => $this->request->getPost('billing_country'),
                'zip' => $this->request->getPost('billing_zip'),
                'created_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s')
            ];

            $addressModel->insert($billingAddress);

            if ($this->request->getPost('copy_address') === 'on') {
                $shippingAddress = $billingAddress;
                $shippingAddress['address_type'] = 'shipping';
            } else {
                $shippingAddress = [
                    'customer_id' => $customerId,
                    'address_type' => 'shipping',
                    'address1' => $this->request->getPost('shipping_address1'),
                    'address2' => $this->request->getPost('shipping_address2'),
                    'city' => $this->request->getPost('shipping_city'),
                    'state' => $this->request->getPost('shipping_state'),
                    'country' => $this->request->getPost('shipping_country'),
                    'zip' => $this->request->getPost('shipping_zip'),
                    'created_time' => date('Y-m-d H:i:s'),
                    'updated_time' => date('Y-m-d H:i:s')
                ];
            }

            $addressModel->insert($shippingAddress);
            $contact_type = 'customer';
            $zohoResponse = $ZohoBooksController->createContact($customerData, $shippingAddress, $billingAddress, $contact_type);
            $responseArray = json_decode($zohoResponse, true);
            if ($responseArray['code'] === 0) {
                // Handle error
                $contact_id = $responseArray['contact']['contact_id'];
                $customerModel->where('customer_id', $customerId)->set('zoho_contact_id', $contact_id)->update();
                return redirect()->to('/customers');
            } else {
                return redirect()->to('/error_page');
            }
        } else {
            $data['pageTitle'] = 'Customer List';  
            return view('customers/create', $data);
        }
    }
    public function edit($id)
    {
        helper(['form']);

        $customerModel = new CustomerModel();
        $addressModel = new CustomerAddressModel();
        $ZohoBooksController = new ZohoBooksController();

        $customer = $customerModel->find($id);
        $billingAddress = $addressModel->where('customer_id', $id)->where('address_type', 'billing')->first();
        $shippingAddress = $addressModel->where('customer_id', $id)->where('address_type', 'shipping')->first();

        if ($this->request->getMethod() === 'POST' && $this->validate([
            'company_name' => 'required',
            'contact_name' => 'required',
            'billing_address1' => 'required',
            'billing_city' => 'required',
            'billing_state' => 'required',
            'billing_country' => 'required',
        ])) {
            $customerData = [
                'company_name' => $this->request->getPost('company_name'),
                'contact_name' => $this->request->getPost('contact_name'),
                'credit_limit' => $this->request->getPost('credit_limit'),
                'tax_type' => $this->request->getPost('tax_type'),
                'gst_no' => $this->request->getPost('gst_no'),
                'preferred_transport' => $this->request->getPost('preferred_transport'),
                'contact_no' => $this->request->getPost('contact_no'),
                'email' => $this->request->getPost('email'),
                'payment_terms' => $this->request->getPost('payment_terms'),
                'updated_time' => date('Y-m-d H:i:s'),
                'customer_sub_type' => $this->request->getPost('gst_no')!= '' ? 'business' : 'individual',
                'zoho_contact_id' => $this->request->getPost('zoho_contact_id')
            ];

            $customerModel->update($id, $customerData);

            $billingAddressData = [
                'address1' => $this->request->getPost('billing_address1'),
                'address2' => $this->request->getPost('billing_address2'),
                'city' => $this->request->getPost('billing_city'),
                'state' => $this->request->getPost('billing_state'),
                'country' => $this->request->getPost('billing_country'),
                'zip' => $this->request->getPost('billing_zip'),
                'updated_time' => date('Y-m-d H:i:s')
            ];

            $addressModel->update($billingAddress['id'], $billingAddressData);

            if ($this->request->getPost('copy_address') === 'on') {
                $shippingAddressData = $billingAddressData;
                $shippingAddressData['address_type'] = 'shipping';
            } else {
                $shippingAddressData = [
                    'address1' => $this->request->getPost('shipping_address1'),
                    'address2' => $this->request->getPost('shipping_address2'),
                    'city' => $this->request->getPost('shipping_city'),
                    'state' => $this->request->getPost('shipping_state'),
                    'country' => $this->request->getPost('shipping_country'),
                    'zip' => $this->request->getPost('shipping_zip'),
                    'updated_time' => date('Y-m-d H:i:s')
                ];
            }

            $addressModel->update($shippingAddress['id'], $shippingAddressData);
            $contact_type = 'customer';
            $zohoResponse = $ZohoBooksController->updateContact($customerData, $shippingAddress, $billingAddress, $contact_type);
            $responseArray = json_decode($zohoResponse, true);
            if ($responseArray['code'] === 0) {
                return redirect()->to('/customers');
            } else {
                return redirect()->to('/error_page');
            }
        } else {
            $data = [
                'customer' => $customer,
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress,
                'pageTitle' => 'Edit Customer'
            ];
            return view('customers/edit', $data);
        }
    }

    public function delete($id)
    {
        $customerModel = new CustomerModel();
        $addressModel = new CustomerAddressModel();

        // Delete the addresses
        $addressModel->where('customer_id', $id)->delete();

        // Delete the customer
        $customerModel->delete($id);

        return redirect()->to('/customers');
    }

    public function view($id)
    {
        $customerModel = new CustomerModel();
        $addressModel = new CustomerAddressModel();

        $customer = $customerModel->find($id);
        $billingAddress = $addressModel->where('customer_id', $id)->where('address_type', 'billing')->first();
        $shippingAddress = $addressModel->where('customer_id', $id)->where('address_type', 'shipping')->first();

        $data = [
            'customer' => $customer,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'pageTitle' => 'Customer List'
        ];

        return view('customers/view', $data);
    }
}
