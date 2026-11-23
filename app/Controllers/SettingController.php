<?php

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\Controller;

class SettingController extends BaseController
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $settings = $this->settingModel->findAll();
        $settingMap = [];
        foreach ($settings as $s) {
            $settingMap[$s['setting_key']] = $s['setting_value'];
        }

        // Fetch Countries for the dropdown
        $countryModel = new \App\Models\CountryModel();
        $countries = $countryModel->orderBy('name', 'ASC')->findAll();

        // Fetch active Bank Accounts for print configuration
        $bankAccountModel = new \App\Models\BankAccountModel();
        $bankAccounts = $bankAccountModel->where('account_type', 'Bank')->where('status', 'active')->findAll();

        // Compute preview of next invoice number
        $invoiceModel = new \App\Models\InvoiceModel();
        $nextInvoicePreview = $invoiceModel->generateInvoiceNumber();

        return view('settings/index', [
            'settings'           => $settingMap,
            'countries'          => $countries,
            'bankAccounts'       => $bankAccounts,
            'nextInvoicePreview' => $nextInvoicePreview
        ]);
    }

    public function update()
    {
        $settings = $this->request->getPost('settings');

        // Handle logo file upload from local
        $logoFile = $this->request->getFile('company_logo_file');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $newName = 'logo_' . time() . '.' . $logoFile->getExtension();
            $uploadPath = FCPATH . 'uploads/company/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $logoFile->move($uploadPath, $newName);
            if (!is_array($settings)) {
                $settings = [];
            }
            $settings['company_logo_url'] = base_url('uploads/company/' . $newName);
        }

        // Handle Bank Account selection array
        $bankIds = $this->request->getPost('print_bank_accounts');
        if (is_array($bankIds)) {
            if (!is_array($settings)) {
                $settings = [];
            }
            $settings['print_selected_bank_ids'] = json_encode(array_values(array_filter($bankIds)));
        }

        if ($settings && is_array($settings)) {
            foreach ($settings as $key => $value) {
                $this->settingModel->updateSetting($key, $value);
            }
            return redirect()->to('settings')->with('success', 'Settings updated successfully.');
        }

        return redirect()->back()->with('error', 'Invalid settings data.');
    }
}
