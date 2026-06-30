<?php

namespace App\Controllers;

class YarnReportController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // 1. Inventory Summary
        $data['inventorySummary'] = $db->table('production_yarn_stock_movements')
            ->select('yarn_count, yarn_type, color, brand_mill, SUM(quantity_kg) as stock, CASE WHEN SUM(quantity_kg) > 0 THEN SUM(quantity_kg * cost_per_kg) / SUM(quantity_kg) ELSE 0 END as avg_cost')
            ->groupBy('yarn_count, yarn_type, color, brand_mill')
            ->having('SUM(quantity_kg) > 0')
            ->get()->getResultArray();

        // 2. Job Work Pending
        $data['pendingDcs'] = $db->table('production_yarn_job_work_dcs')
            ->select('production_yarn_job_work_dcs.*, SUM(quantity_issued_kg) as total_issued, SUM(quantity_received_kg) as total_received')
            ->join('production_yarn_job_work_dc_items', 'production_yarn_job_work_dc_items.dc_id = production_yarn_job_work_dcs.id')
            ->whereIn('production_yarn_job_work_dcs.status', ['Open', 'Partially Received'])
            ->groupBy('production_yarn_job_work_dcs.id')
            ->get()->getResultArray();

        // 3. Wastage Report (Vendor-wise)
        $data['vendorWastage'] = $db->table('production_yarn_job_work_dcs')
            ->select('vendor_name, SUM(quantity_issued_kg) as issued, SUM(quantity_received_kg) as received, SUM(quantity_wastage_kg) as wastage')
            ->join('production_yarn_job_work_dc_items', 'production_yarn_job_work_dc_items.dc_id = production_yarn_job_work_dcs.id')
            ->groupBy('vendor_name')
            ->get()->getResultArray();

        // 4. Costing & Valuation
        $data['valuation'] = $db->table('production_yarn_stock_movements')
            ->select('yarn_count, yarn_type, color, SUM(quantity_kg) as stock, SUM(quantity_kg * cost_per_kg) as total_value')
            ->groupBy('yarn_count, yarn_type, color')
            ->having('SUM(quantity_kg) > 0')
            ->get()->getResultArray();

        $data['title'] = 'Yarn Inventory & Job Work Reports';
        return view('production/yarn_reports/index', $data);
    }
}
