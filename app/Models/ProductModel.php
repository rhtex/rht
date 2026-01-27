<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_name', 'barcode', 'category_id', 'hsn_code', 'description', 'unit', 'selling_price', 'tax_id', 'total_stock', 'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'product_name' => 'required|min_length[2]|max_length[255]',
        'category_id'  => 'required|integer',
        'status'       => 'required|in_list[active,inactive]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    public function getProductsWithCategory()
    {
        return $this->select('products.*, product_categories.category_name, product_images.image_path as primary_image, taxes.percentage as tax_percentage')
                    ->join('product_categories', 'product_categories.id = products.category_id', 'left')
                    ->join('taxes', 'taxes.id = products.tax_id', 'left')
                    ->join('product_images', 'product_images.product_id = products.id AND product_images.is_primary = 1', 'left')
                    ->findAll();
    }

    public function getFilteredProducts($filters = [])
    {
        $builder = $this->select('products.*, product_categories.category_name, product_images.image_path as primary_image, taxes.percentage as tax_percentage')
                        ->join('product_categories', 'product_categories.id = products.category_id', 'left')
                        ->join('taxes', 'taxes.id = products.tax_id', 'left')
                        ->join('product_images', 'product_images.product_id = products.id AND product_images.is_primary = 1', 'left');

        if (!empty($filters['category_id'])) {
            $builder->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('products.product_name', $filters['search'])
                    ->orLike('products.hsn_code', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('products.status', $filters['status']);
        }

        return $builder->orderBy('products.id', 'DESC')->findAll();
    }

    public function updateStock($productId)
    {
        $itemModel = new ProductItemModel();
        $count = $itemModel->where('product_id', $productId)
                           ->where('status', 'available')
                           ->countAllResults();
        
        return $this->update($productId, ['total_stock' => $count]);
    }
}
