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

        return view('settings/index', [
            'settings' => $settingMap
        ]);
    }

    public function update()
    {
        $settings = $this->request->getPost('settings');

        if ($settings && is_array($settings)) {
            foreach ($settings as $key => $value) {
                $this->settingModel->updateSetting($key, $value);
            }
            return redirect()->to('settings')->with('success', 'Settings updated successfully.');
        }

        return redirect()->back()->with('error', 'Invalid settings data.');
    }
}
