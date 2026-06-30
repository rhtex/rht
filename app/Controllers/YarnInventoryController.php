<?php

namespace App\Controllers;

use App\Models\YarnStockMovementModel;

class YarnInventoryController extends BaseController
{
    protected $movementModel;

    public function __construct()
    {
        $this->movementModel = new YarnStockMovementModel();
    }

    public function index()
    {
        $filters = [
            'search'     => $this->request->getGet('search'),
            'yarn_type'  => $this->request->getGet('yarn_type'),
            'color'      => $this->request->getGet('color'),
            'yarn_count' => $this->request->getGet('yarn_count'),
            'brand_mill' => $this->request->getGet('brand_mill'),
            'csp'        => $this->request->getGet('csp'),
            'lot_number' => $this->request->getGet('lot_number'),
            'warp_weft'  => $this->request->getGet('warp_weft'),
        ];

        $data['inventory'] = $this->movementModel->getInventory($filters);
        $data['title'] = 'Yarn Inventory';
        $data['filters'] = $filters;

        return view('production/yarn_inventory/index', $data);
    }

    public function history()
    {
        $name = $this->request->getGet('name');
        $count = $this->request->getGet('count');
        $type = $this->request->getGet('type');
        $color = $this->request->getGet('color');
        $mill = $this->request->getGet('mill');
        $lot = $this->request->getGet('lot');
        $csp = $this->request->getGet('csp');
        $warp_weft = $this->request->getGet('warp_weft');
        $warehouse = $this->request->getGet('warehouse');

        $conditions = [
            'yarn_count' => $count,
            'yarn_type'  => $type,
            'color'      => $color,
            'brand_mill' => $mill,
            'warp_weft'  => $warp_weft,
        ];

        if ($lot === '') {
            $conditions['lot_number'] = null;
        } else {
            $conditions['lot_number'] = $lot;
        }

        if ($csp === '') {
            $conditions['csp'] = null;
        } else {
            $conditions['csp'] = $csp;
        }

        if ($warehouse === '') {
            $conditions['warehouse'] = null;
        } else {
            $conditions['warehouse'] = $warehouse;
        }

        $builder = $this->movementModel->builder();
        $builder->where($conditions);

        $data['transactions'] = $builder->orderBy('created_at', 'ASC')->get()->getResultArray();
        $data['title'] = 'Stock Transaction History';
        $data['yarn_details'] = [
            'name'      => $name,
            'count'     => $count,
            'type'      => $type,
            'color'     => $color,
            'mill'      => $mill,
            'lot'       => $lot,
            'csp'       => $csp,
            'warp_weft' => $warp_weft,
            'warehouse' => $warehouse
        ];

        return view('production/yarn_inventory/history', $data);
    }
}
