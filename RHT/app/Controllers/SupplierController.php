<?php

namespace App\Controllers;

use App\Models\SupplierModel;
use App\Models\SupplierAddressModel;
use CodeIgniter\Controller;

class SupplierController extends Controller
{
    protected $supplierModel;
    protected $supplierAddressModel;

    public function __construct()
    {
        helper(['form']);
        helper('state');
        $this->supplierModel = new SupplierModel();
        $this->supplierAddressModel = new SupplierAddressModel();
    }
    public function index()
    {
        $model = new SupplierModel();
        $data['suppliers'] = $model->findAll();

        return view('/suppliers/index', $data);
    }

    public function create()
    {
        $supplierModel = new SupplierModel();
        $ZohoBooksController = new ZohoBooksController();
        if ($this->request->getMethod() === 'POST' && $this->validate([
            'contact_name' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
        ])) {
            $supplierData = [
                'company_name' => $this->request->getPost('company_name'),
                'contact_name' => $this->request->getPost('contact_name'),
                'tax_type' => $this->request->getPost('tax_type'),
                'msme_registered' => $this->request->getPost('msme_registered'),
                'gst_no' => $this->request->getPost('gst_no'),
                'payment_terms' => $this->request->getPost('payment_terms'),
                'contact_no' => $this->request->getPost('contact_no'),
                'email' => $this->request->getPost('email'),
                'created_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s')
            ];

            $this->supplierModel->insert($supplierData);
            $supplierId = $this->supplierModel->insertID();

            $address = [
                'supplier_id' => $supplierId,
                'address1' => $this->request->getPost('address1'),
                'address2' => $this->request->getPost('address2'),
                'city' => $this->request->getPost('city'),
                'state' => $this->request->getPost('state'),
                'zip' => $this->request->getPost('zip'),
                'country' => $this->request->getPost('country'),
                'created_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s')
            ];

            $this->supplierAddressModel->insert($address);
            $contact_type = 'vendor';
            $zohoResponse = $ZohoBooksController->createContact($supplierData, $address, $address, $contact_type);
            $responseArray = json_decode($zohoResponse, true);
            if ($responseArray['code'] === 0) {
                // Handle error
                $contact_id = $responseArray['contact']['contact_id'];
                $supplierModel->where('supplier_id', $supplierId)->set('zoho_contact_id', $contact_id)->update();
                return redirect()->to('/suppliers');
            } else {
                return redirect()->to('/error_page');
            }
        } else {
            return view('suppliers/create');
        }
    }
    public function getSuppliers()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('suppliers');
        $builder->select('suppliers.*, supplier_address.city');
        $builder->join('supplier_address', 'suppliers.supplier_id = supplier_address.supplier_id');
        $query = $builder->get();
        $suppliers = $query->getResult();

        return $this->response->setJSON($suppliers);
    }
    public function edit($id)
    {
        $supplier = $this->supplierModel->find($id);
        $supplierAddress = $this->supplierAddressModel->where('supplier_id', $id)->first();

        if ($supplierAddress) {
            $supplier = array_merge($supplier, $supplierAddress);
        }

        return view('suppliers/edit', ['supplier' => $supplier]);
    }

    public function update($id)
    {
        $ZohoBooksController = new ZohoBooksController();
        $supplierData = [
            'company_name' => $this->request->getPost('company_name'),
            'contact_name' => $this->request->getPost('contact_name'),
            'tax_type' => $this->request->getPost('tax_type'),
            'msme_registered' => $this->request->getPost('msme_registered'),
            'gst_no' => $this->request->getPost('gst_no'),
            'contact_no' => $this->request->getPost('contact_no'),
            'email' => $this->request->getPost('email'),
            'payment_terms' => $this->request->getPost('payment_terms'),
            'msme_registered' => $this->request->getPost('msme_registered'),
            'updated_time' => date('Y-m-d H:i:s'),
            'zoho_contact_id' => $this->request->getPost('zoho_contact_id'),
            'customer_sub_type' => $this->request->getPost('gst_no')!= '' ? 'business' : 'individual',
        ];

        $this->supplierModel->update($id, $supplierData);

        $supplierAddressData = [
            'address1' => $this->request->getPost('address1'),
            'address2' => $this->request->getPost('address2'),
            'city' => $this->request->getPost('city'),
            'state' => $this->request->getPost('state'),
            'zip' => $this->request->getPost('zip'),
            'country' => $this->request->getPost('country'),
            'updated_time' => date('Y-m-d H:i:s')
        ];

        $this->supplierAddressModel->where('supplier_id', $id)->set($supplierAddressData)->update();
        $contact_type = 'vendor';
        $zohoResponse = $ZohoBooksController->updateContact($supplierData, $supplierAddressData, $supplierAddressData, $contact_type);
        $responseArray = json_decode($zohoResponse, true);
        if ($responseArray['code'] === 0) {
            return redirect()->to('/suppliers');
        } else {
            return redirect()->to('/error_page');
        }
    }
}
