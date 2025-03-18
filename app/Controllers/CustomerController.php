<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\CustomerAddressModel;
use CodeIgniter\Controller;

class CustomerController extends Controller
{
    /**
     * Lists all customers with permission checks.
     */
    public function index()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Customer', 'read');

        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            return view('/signin', $data);
        } elseif (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            $model = new CustomerModel();
            $data['customers'] = $model->findAll();
            $data['pageTitle'] = 'Customer List';
            return view('/customers/list', $data);
        }
    }

    /**
     * Handles customer creation with permission checks.
     */
    public function create()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Customer', 'create');

        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            return view('/signin', $data);
        } elseif (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
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
                    $contact_id = $responseArray['contact']['contact_id'];
                    $customerModel->where('customer_id', $customerId)->set('zoho_contact_id', $contact_id)->update();
                    return redirect()->to('/customers');
                } else {
                    return redirect()->to('/error_page');
                }
            } else {
                $data['pageTitle'] = 'Create Customer';
                return view('customers/create', $data);
            }
        }
    }

    /**
     * Additional methods (edit, delete, view) with similar permission checks would go here.
     */
    public function view($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Customer', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } elseif (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            $customerModel = new CustomerModel();
            $addressModel = new CustomerAddressModel();

            $customer = $customerModel->find($id);
            $billingAddress = $addressModel->where('customer_id', $id)->where('address_type', 'billing')->first();
            $shippingAddress = $addressModel->where('customer_id', $id)->where('address_type', 'shipping')->first();

            $data = [
                'pageTitle' => 'View Customer',
                'customer' => $customer,
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress
            ];

            return view('customers/view', $data);
        }
    }

    public function edit($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Customer', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } elseif (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            helper(['form']);
            $customerModel = new CustomerModel();
            $addressModel = new CustomerAddressModel();

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
                    'updated_time' => date('Y-m-d H:i:s')
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

                $shippingAddressData = [
                    'address1' => $this->request->getPost('shipping_address1'),
                    'address2' => $this->request->getPost('shipping_address2'),
                    'city' => $this->request->getPost('shipping_city'),
                    'state' => $this->request->getPost('shipping_state'),
                    'country' => $this->request->getPost('shipping_country'),
                    'zip' => $this->request->getPost('shipping_zip'),
                    'updated_time' => date('Y-m-d H:i:s')
                ];

                $addressModel->update($shippingAddress['id'], $shippingAddressData);

                session()->setFlashdata('success', 'Customer updated successfully.');
                return redirect()->to('/customers');
            }

            $data = [
                'pageTitle' => 'Edit Customer',
                'customer' => $customer,
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress
            ];

            return view('customers/edit', $data);
        }
    }

    public function delete($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Customer', 'delete');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } elseif (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            $customerModel = new CustomerModel();
            $addressModel = new CustomerAddressModel();

            $addressModel->where('customer_id', $id)->delete();
            $customerModel->delete($id);

            session()->setFlashdata('success', 'Customer deleted successfully.');
            return redirect()->to('/customers');
        }
    }
}
