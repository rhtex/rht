<?php

if (!function_exists('get_setting')) {
    /**
     * Retrieve a setting value from the database
     */
    function get_setting(string $key, $default = '')
    {
        $settingModel = new \App\Models\SettingModel();
        return $settingModel->getSetting($key, $default);
    }
}
