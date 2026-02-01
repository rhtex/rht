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

        // Clean input
        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }

        // 1. Update basic settings first (Client ID, Secret, etc.)
        if ($this->zohoSettingsModel->update(1, $data)) {

            // 2. Handle Grant Token Exchange if provided
            if (!empty($data['grant_token'])) {
                $authService = new \App\Services\ZohoAuthService();
                $result = $authService->generateTokensFromGrant($data['grant_token']);
                if ($result === true) {
                    return redirect()->to('zoho-settings')->with('success', 'Settings updated and Grant Token exchanged successfully!');
                } else {
                    return redirect()->to('zoho-settings')->with('warning', 'Settings updated, but Grant Token exchange failed: ' . $result);
                }
            }

            return redirect()->to('zoho-settings')->with('success', 'Zoho settings updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update settings.');
    }
}
