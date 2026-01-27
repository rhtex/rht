<?php

namespace App\Controllers;

use App\Models\StateModel;
use CodeIgniter\API\ResponseTrait;

class MasterDataController extends BaseController
{
    use ResponseTrait;

    public function getStatesByCountry($countryId)
    {
        try {
            $stateModel = new StateModel();
            
            // Fetch states for the country. Removed status check to debug visibility issues.
            // Also ordered by name.
            $states = $stateModel->where('country_id', $countryId)
                                ->orderBy('name', 'ASC')
                                ->findAll();
            
            // If no states found, return empty array (which is valid for JS)
            if (empty($states)) {
                 return $this->respond([]);
            }

            return $this->respond($states);
            
        } catch (\Exception $e) {
            // Log the error
            log_message('error', 'Error fetching states: ' . $e->getMessage());
            // Return validation error or internal error
            return $this->fail('Failed to load states', 500);
        }
    }
}
