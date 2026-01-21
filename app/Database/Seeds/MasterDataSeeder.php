<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Countries
        $countries = [
            ['name' => 'India', 'iso_code_2' => 'IN', 'iso_code_3' => 'IND', 'phone_code' => '91'],
            ['name' => 'United States', 'iso_code_2' => 'US', 'iso_code_3' => 'USA', 'phone_code' => '1'],
            ['name' => 'United Kingdom', 'iso_code_2' => 'GB', 'iso_code_3' => 'GBR', 'phone_code' => '44'],
            // Add more as needed, or use a fuller list if available
        ];

        foreach ($countries as $country) {
            $this->db->table('countries')->insert($country);
            $countryId = $this->db->insertID();

            // 2. States for India
            if ($country['name'] === 'India') {
                $states = [
                    ['name' => 'Tamil Nadu', 'state_code' => 'TN', 'gst_state_code' => '33'],
                    ['name' => 'Kerala', 'state_code' => 'KL', 'gst_state_code' => '32'],
                    ['name' => 'Karnataka', 'state_code' => 'KA', 'gst_state_code' => '29'],
                    ['name' => 'Andhra Pradesh', 'state_code' => 'AP', 'gst_state_code' => '28'],
                    ['name' => 'Maharashtra', 'state_code' => 'MH', 'gst_state_code' => '27'],
                    ['name' => 'Delhi', 'state_code' => 'DL', 'gst_state_code' => '07'],
                ];

                foreach ($states as $state) {
                    $state['country_id'] = $countryId;
                    $this->db->table('states')->insert($state);
                }
            }
        }
    }
}
