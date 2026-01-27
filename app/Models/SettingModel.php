<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'setting_key';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['setting_key', 'setting_value'];

    protected $useTimestamps = true;

    public function getSetting(string $key, $default = '')
    {
        $setting = $this->find($key);
        return $setting ? $setting['setting_value'] : $default;
    }

    public function updateSetting(string $key, $value)
    {
        $existing = $this->find($key);
        if ($existing) {
            return $this->update($key, ['setting_value' => $value]);
        } else {
            return $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value
            ]);
        }
    }
    public function getAllSettings()
    {
        $settings = $this->findAll();
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting['setting_key']] = $setting['setting_value'];
        }
        return $data;
    }
}
