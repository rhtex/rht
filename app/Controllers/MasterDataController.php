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

    public function getProductByBarcode()
    {
        $barcode = $this->request->getGet('barcode');

        if (!$barcode) {
            return $this->fail('Barcode is required', 400);
        }

        try {
            $productModel = new \App\Models\ProductModel();
            $product = $productModel->where('barcode', $barcode)->first();

            if (!$product) {
                return $this->failNotFound('Product not found');
            }

            return $this->respond([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Barcode scan error: ' . $e->getMessage());
            return $this->fail('Server error', 500);
        }
    }
}
