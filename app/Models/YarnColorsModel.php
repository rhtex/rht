<?php

namespace App\Models;

use CodeIgniter\Model;

class YarnColorsModel extends Model
{
    protected $table = 'yarn_colors';  // Name of your table
    protected $primaryKey = 'id';       // Primary key of the table
    protected $allowedFields = [
        'body_color_name', 
        'border_color_name', 
        'weft_color_name', 
        'body_color_code', 
        'border_color_code', 
        'weft_color_code', 
        'approval'
    ]; // Fields that can be inserted or updated

    // Optional: You can also add validation rules if needed
    protected $validationRules = [
        'body_color_name' => 'required|max_length[50]',
        'border_color_name' => 'required|max_length[50]',
        'weft_color_name' => 'required|max_length[50]',
        'body_color_code' => 'required|max_length[10]',
        'border_color_code' => 'required|max_length[10]',
        'weft_color_code' => 'required|max_length[10]',
        'approval' => 'permit_empty|max_length[10]'
    ];

    protected $validationMessages = [
        'body_color_name' => [
            'required' => 'Body color name is required.',
            'max_length' => 'Body color name cannot exceed 50 characters.'
        ],
        'border_color_name' => [
            'required' => 'Border color name is required.',
            'max_length' => 'Border color name cannot exceed 50 characters.'
        ],
        'weft_color_name' => [
            'required' => 'Weft color name is required.',
            'max_length' => 'Weft color name cannot exceed 50 characters.'
        ],
        'body_color_code' => [
            'required' => 'Body color code is required.',
            'max_length' => 'Body color code cannot exceed 10 characters.'
        ],
        'border_color_code' => [
            'required' => 'Border color code is required.',
            'max_length' => 'Border color code cannot exceed 10 characters.'
        ],
        'weft_color_code' => [
            'required' => 'Weft color code is required.',
            'max_length' => 'Weft color code cannot exceed 10 characters.'
        ],
        'approval' => [
            'max_length' => 'Approval status cannot exceed 10 characters.'
        ]
    ];

    // Optional: You can add additional methods for custom queries or operations
}
