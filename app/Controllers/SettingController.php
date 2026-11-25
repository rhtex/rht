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

    private const ALLOWED_KEYS = [
        'app_name',
        'org_name',
        'company_address_1',
        'company_address_2',
        'company_city',
        'company_country',
        'company_state',
        'company_pincode',
        'company_gstin',
        'company_gst_type',
        'company_email',
        'company_phone',
        'company_mobile',
        'company_whatsapp',
        'invoice_number_format',
        'invoice_prefix',
        'invoice_padding_digits',
        'invoice_next_number',
        'company_print_name',
        'company_print_gstin',
        'company_upi_id',
        'company_upi_name',
        'company_print_address',
        'company_print_phone',
        'company_print_email',
        'company_logo_url',
        'print_header_font',
        'print_font_size',
        'print_selected_bank_ids',
        'print_default_terms',
    ];

    public function update()
    {
        $rawSettings = $this->request->getPost('settings');
        $settings = is_array($rawSettings) ? array_intersect_key($rawSettings, array_flip(self::ALLOWED_KEYS)) : [];

        // Handle logo file upload from local with strict image validation
        $logoFile = $this->request->getFile('company_logo_file');
        if ($logoFile && $logoFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$this->validate([
                'company_logo_file' => 'uploaded[company_logo_file]|is_image[company_logo_file]|mime_in[company_logo_file,image/png,image/jpeg,image/webp]|ext_in[company_logo_file,png,jpg,jpeg,webp]|max_size[company_logo_file,2048]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            if ($logoFile->isValid() && !$logoFile->hasMoved()) {
                $newName = 'logo_' . time() . '.' . strtolower($logoFile->getExtension());
                $uploadPath = FCPATH . 'uploads/company/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $logoFile->move($uploadPath, $newName);
                $settings['company_logo_url'] = base_url('uploads/company/' . $newName);
            }
        }

        // Always sync Bank Account selection array (clears when all checkboxes are unchecked)
        $bankIds = $this->request->getPost('print_bank_accounts');
        $settings['print_selected_bank_ids'] = json_encode(
            is_array($bankIds) ? array_values(array_filter($bankIds)) : []
        );

        if (!empty($settings)) {
            foreach ($settings as $key => $value) {
                $this->settingModel->updateSetting($key, $value);
            }
            return redirect()->to('settings')->with('success', 'Settings updated successfully.');
        }

        return redirect()->back()->with('error', 'Invalid settings data.');
    }
}
