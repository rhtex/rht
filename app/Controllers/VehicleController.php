<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\VehicleModel;

class VehicleController extends BaseController
{
    protected $vehicleModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
    }

    /**
     * List all vehicles
     */
    public function index()
    {
        $search = trim($this->request->getGet('search') ?? '');

        $builder = $this->vehicleModel;
        if (!empty($search)) {
            $builder = $builder->groupStart()
                ->like('vehicle_number', $search)
                ->orLike('vehicle_name', $search)
                ->orLike('vehicle_model', $search)
                ->orLike('vehicle_type', $search)
                ->groupEnd();
        }

        $vehicles = $builder->orderBy('id', 'DESC')->paginate(25);

        $totalVehicles = $this->vehicleModel->countAllResults(false);
        $activeCount   = (clone $this->vehicleModel)->where('status', 'active')->countAllResults();
        $regularCount  = (clone $this->vehicleModel)->where('vehicle_type', 'Regular')->countAllResults();

        return view('vehicles/index', [
            'title'    => 'Vehicles Management',
            'vehicles' => $vehicles,
            'pager'    => $this->vehicleModel->pager,
            'search'   => $search,
            'stats'    => [
                'total'   => $totalVehicles,
                'active'  => $activeCount,
                'regular' => $regularCount,
            ],
        ]);
    }

    /**
     * Show vehicle creation form
     */
    public function create()
    {
        return view('vehicles/form', [
            'title'   => 'Add New Vehicle',
            'vehicle' => null,
            'action'  => site_url('vehicles/store'),
        ]);
    }

    /**
     * Store vehicle in database
     */
    public function store()
    {
        $input = $this->request->getPost();
        $input['vehicle_number'] = strtoupper(trim(str_replace(' ', '', $input['vehicle_number'] ?? '')));

        if (!$this->validate([
            'vehicle_number' => 'required|min_length[3]|max_length[50]',
            'vehicle_type'   => 'required|in_list[Regular,Over Dimensional Cargo]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $input['created_by'] = session('user_id') ? (int) session('user_id') : 1;
        $input['status']     = $input['status'] ?? 'active';

        $this->vehicleModel->insert($input);

        return redirect()->to(site_url('vehicles'))->with('success', 'Vehicle added successfully.');
    }

    /**
     * Show vehicle edit form
     */
    public function edit($id)
    {
        $vehicle = $this->vehicleModel->find((int) $id);
        if (!$vehicle) {
            return redirect()->to(site_url('vehicles'))->with('error', 'Vehicle record not found.');
        }

        return view('vehicles/form', [
            'title'   => 'Edit Vehicle: ' . ($vehicle['vehicle_number'] ?? ''),
            'vehicle' => $vehicle,
            'action'  => site_url('vehicles/update/' . $vehicle['id']),
        ]);
    }

    /**
     * Update vehicle in database
     */
    public function update($id)
    {
        $vehicle = $this->vehicleModel->find((int) $id);
        if (!$vehicle) {
            return redirect()->to(site_url('vehicles'))->with('error', 'Vehicle record not found.');
        }

        $input = $this->request->getPost();
        $input['vehicle_number'] = strtoupper(trim(str_replace(' ', '', $input['vehicle_number'] ?? '')));

        if (!$this->validate([
            'vehicle_number' => 'required|min_length[3]|max_length[50]',
            'vehicle_type'   => 'required|in_list[Regular,Over Dimensional Cargo]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $input['updated_by'] = session('user_id') ? (int) session('user_id') : 1;
        $input['status']     = $input['status'] ?? 'active';

        $this->vehicleModel->update((int) $id, $input);

        return redirect()->to(site_url('vehicles'))->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Delete vehicle
     */
    public function delete($id)
    {
        $vehicle = $this->vehicleModel->find((int) $id);
        if (!$vehicle) {
            return redirect()->to(site_url('vehicles'))->with('error', 'Vehicle record not found.');
        }

        $this->vehicleModel->delete((int) $id);

        return redirect()->to(site_url('vehicles'))->with('success', 'Vehicle deleted successfully.');
    }
}
