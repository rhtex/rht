<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IndiaStatesSeeder extends Seeder
{
    public function run()
    {
        $country = $this->db->table('countries')->where('name', 'India')->get()->getRow();
        
        if (!$country) {
            return;
        }

        $countryId = $country->id;

        $states = [
            ['name' => 'Jammu and Kashmir', 'state_code' => 'JK', 'gst_state_code' => '01'],
            ['name' => 'Himachal Pradesh', 'state_code' => 'HP', 'gst_state_code' => '02'],
            ['name' => 'Punjab', 'state_code' => 'PB', 'gst_state_code' => '03'],
            ['name' => 'Chandigarh', 'state_code' => 'CH', 'gst_state_code' => '04'],
            ['name' => 'Uttarakhand', 'state_code' => 'UT', 'gst_state_code' => '05'],
            ['name' => 'Haryana', 'state_code' => 'HR', 'gst_state_code' => '06'],
            ['name' => 'Delhi', 'state_code' => 'DL', 'gst_state_code' => '07'],
            ['name' => 'Rajasthan', 'state_code' => 'RJ', 'gst_state_code' => '08'],
            ['name' => 'Uttar Pradesh', 'state_code' => 'UP', 'gst_state_code' => '09'],
            ['name' => 'Bihar', 'state_code' => 'BR', 'gst_state_code' => '10'],
            ['name' => 'Sikkim', 'state_code' => 'SK', 'gst_state_code' => '11'],
            ['name' => 'Arunachal Pradesh', 'state_code' => 'AR', 'gst_state_code' => '12'],
            ['name' => 'Nagaland', 'state_code' => 'NL', 'gst_state_code' => '13'],
            ['name' => 'Manipur', 'state_code' => 'MN', 'gst_state_code' => '14'],
            ['name' => 'Mizoram', 'state_code' => 'MZ', 'gst_state_code' => '15'],
            ['name' => 'Tripura', 'state_code' => 'TR', 'gst_state_code' => '16'],
            ['name' => 'Meghalaya', 'state_code' => 'ML', 'gst_state_code' => '17'],
            ['name' => 'Assam', 'state_code' => 'AS', 'gst_state_code' => '18'],
            ['name' => 'West Bengal', 'state_code' => 'WB', 'gst_state_code' => '19'],
            ['name' => 'Jharkhand', 'state_code' => 'JH', 'gst_state_code' => '20'],
            ['name' => 'Odisha', 'state_code' => 'OR', 'gst_state_code' => '21'],
            ['name' => 'Chhattisgarh', 'state_code' => 'CT', 'gst_state_code' => '22'],
            ['name' => 'Madhya Pradesh', 'state_code' => 'MP', 'gst_state_code' => '23'],
            ['name' => 'Gujarat', 'state_code' => 'GJ', 'gst_state_code' => '24'],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'state_code' => 'DN', 'gst_state_code' => '26'],
            ['name' => 'Maharashtra', 'state_code' => 'MH', 'gst_state_code' => '27'],
            ['name' => 'Andhra Pradesh', 'state_code' => 'AP', 'gst_state_code' => '28'],
            ['name' => 'Karnataka', 'state_code' => 'KA', 'gst_state_code' => '29'],
            ['name' => 'Goa', 'state_code' => 'GA', 'gst_state_code' => '30'],
            ['name' => 'Lakshadweep', 'state_code' => 'LD', 'gst_state_code' => '31'],
            ['name' => 'Kerala', 'state_code' => 'KL', 'gst_state_code' => '32'],
            ['name' => 'Tamil Nadu', 'state_code' => 'TN', 'gst_state_code' => '33'],
            ['name' => 'Puducherry', 'state_code' => 'PY', 'gst_state_code' => '34'],
            ['name' => 'Andaman and Nicobar Islands', 'state_code' => 'AN', 'gst_state_code' => '35'],
            ['name' => 'Telangana', 'state_code' => 'TG', 'gst_state_code' => '36'],
            ['name' => 'Ladakh', 'state_code' => 'LA', 'gst_state_code' => '37'],
        ];

        foreach ($states as $state) {
            $existing = $this->db->table('states')
                ->where('country_id', $countryId)
                ->where('name', $state['name'])
                ->get()
                ->getRow();
            
            if (!$existing) {
                $state['country_id'] = $countryId;
                $state['status'] = 'active';
                $state['created_at'] = date('Y-m-d H:i:s');
                $state['updated_at'] = date('Y-m-d H:i:s');
                $this->db->table('states')->insert($state);
            } else {
                // Update if exists to ensure codes are correct
                $this->db->table('states')
                    ->where('id', $existing->id)
                    ->update([
                        'state_code' => $state['state_code'],
                        'gst_state_code' => $state['gst_state_code'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
        }
    }
}
