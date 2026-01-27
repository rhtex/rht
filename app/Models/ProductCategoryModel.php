<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductCategoryModel extends Model
{
    protected $table            = 'product_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['category_name', 'parent_id', 'description', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'category_name' => 'required|min_length[2]|max_length[100]',
        'status'        => 'required|in_list[active,inactive]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    public function getSubCategories($parentId)
    {
        return $this->where('parent_id', $parentId)->findAll();
    }

    public function getRootCategories()
    {
        return $this->where('parent_id', null)->findAll();
    }

    /**
     * Get categories in a flat list with indentation to represent hierarchy
     * Used for dropdowns
     */
    public function getCategoryTree($parentId = null, $level = 0, &$tree = [])
    {
        $categories = $this->where('parent_id', $parentId)->findAll();

        foreach ($categories as $category) {
            $category['level'] = $level;
            $category['indented_name'] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) . '- ' . $category['category_name'];
            $tree[] = $category;
            
            // Recurse to get children
            $this->getCategoryTree($category['id'], $level + 1, $tree);
        }

        return $tree;
    }

    /**
     * Get all categories with parent names and their nesting level
     */
    public function getAllCategoriesWithNesting()
    {
        $all = $this->findAll();
        $idMap = [];
        foreach ($all as $cat) {
            $idMap[$cat['id']] = $cat;
        }

        foreach ($all as &$cat) {
            $cat['parent_name'] = isset($idMap[$cat['parent_id']]) ? $idMap[$cat['parent_id']]['category_name'] : '-';
            
            // Calculate level
            $level = 0;
            $currentParentId = $cat['parent_id'];
            while ($currentParentId !== null && isset($idMap[$currentParentId])) {
                $level++;
                $currentParentId = $idMap[$currentParentId]['parent_id'];
            }
            $cat['level'] = $level;
        }

        return $all;
    }

    public function getCategoryPaths()
    {
        $all = $this->findAll();
        $idMap = [];
        foreach ($all as $cat) {
            $idMap[$cat['id']] = $cat;
        }

        $paths = [];
        foreach ($all as $cat) {
            $path = [$cat['category_name']];
            $currentParentId = $cat['parent_id'];
            while ($currentParentId !== null && isset($idMap[$currentParentId])) {
                array_unshift($path, $idMap[$currentParentId]['category_name']);
                $currentParentId = $idMap[$currentParentId]['parent_id'];
            }
            $paths[$cat['id']] = implode(' &gt; ', $path);
        }

        return $paths;
    }
}
