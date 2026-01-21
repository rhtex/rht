<?php

namespace App\Controllers;

use App\Models\ZohoSettingsModel;

class ZohoSettingsController extends BaseController
{
    protected $zohoSettingsModel;

    public function __construct()
    {
        $this->zohoSettingsModel = new ZohoSettingsModel();
    }

    public function index()
    {
        $data['settings'] = $this->zohoSettingsModel->getSettings();
        $data['title'] = 'Zoho Books Integration Settings';
        return view('zoho_settings/index', $data);
    }

    public function update()
    {
        $data = $this->request->getPost();
        
        if ($this->zohoSettingsModel->update(1, $data)) {
            return redirect()->to('zoho-settings')->with('success', 'Zoho settings updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update settings.');
    }
}
