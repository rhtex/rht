<?php

namespace App\Controllers;

use App\Models\StateModel;
use CodeIgniter\API\ResponseTrait;

class MasterDataController extends BaseController
{
    use ResponseTrait;

    public function getStatesByCountry($countryId)
    {
        $stateModel = new StateModel();
        $states = $stateModel->where('country_id', $countryId)
                            ->where('status', 'active')
                            ->orderBy('name', 'ASC')
                            ->findAll();

        return $this->respond($states);
    }
}
