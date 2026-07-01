<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnStockMovementModel extends Model
{
    protected $table            = 'production_yarn_stock_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'yarn_name', 'yarn_count', 'yarn_type', 'color', 'brand_mill', 'lot_number', 'csp', 'warp_weft', 
        'quantity_kg', 'quantity_cones', 'cost_per_kg', 'warehouse', 'movement_type', 'reference_id', 'remarks', 'created_by', 'created_at'
    ];

    protected $useTimestamps = false;

    /**
     * Get real-time available inventory by grouping
     */
    public function getInventory($filters = [])
    {
        $builder = $this->db->table($this->table);
        $builder->select('
            MAX(yarn_name) as yarn_name, 
            yarn_count, 
            yarn_type, 
            color, 
            brand_mill, 
            lot_number, 
            csp, 
            warp_weft, 
            SUM(quantity_kg) as quantity_available,
            SUM(quantity_cones) as cones_available,
            CASE 
                WHEN SUM(quantity_kg) > 0 THEN SUM(quantity_kg * cost_per_kg) / SUM(quantity_kg)
                ELSE 0 
            END as avg_cost_per_kg,
            warehouse
        ');
        $builder->groupBy('yarn_count, yarn_type, color, brand_mill, lot_number, csp, warp_weft, warehouse');
        
        if (isset($filters['show_unavailable']) && $filters['show_unavailable'] === 'yes') {
            // Show all stock levels including 0 and negative
        } else {
            $builder->having('SUM(quantity_kg) > 0');
        }

        if (!empty($filters['yarn_type'])) {
            $builder->where('yarn_type', $filters['yarn_type']);
        }
        if (!empty($filters['color'])) {
            $builder->like('color', $filters['color']);
        }
        if (!empty($filters['yarn_count'])) {
            $builder->where('yarn_count', $filters['yarn_count']);
        }
        if (!empty($filters['brand_mill'])) {
            $builder->like('brand_mill', $filters['brand_mill']);
        }
        if (!empty($filters['csp'])) {
            $builder->where('csp', $filters['csp']);
        }
        if (!empty($filters['lot_number'])) {
            $builder->where('lot_number', $filters['lot_number']);
        }
        if (!empty($filters['warp_weft'])) {
            $builder->where('warp_weft', $filters['warp_weft']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('yarn_name', $filters['search'])
                    ->orLike('yarn_count', $filters['search'])
                    ->orLike('color', $filters['search'])
                    ->orLike('brand_mill', $filters['search'])
                    ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }
}
