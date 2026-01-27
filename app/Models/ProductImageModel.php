<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table            = 'product_images';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['product_id', 'image_path', 'is_primary'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'product_id' => 'required|integer',
        'image_path' => 'required',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    public function getProductImages($productId)
    {
        return $this->where('product_id', $productId)->findAll();
    }

    public function setPrimary($imageId, $productId)
    {
        $this->where('product_id', $productId)->set(['is_primary' => 0])->update();
        return $this->update($imageId, ['is_primary' => 1]);
    }
}
