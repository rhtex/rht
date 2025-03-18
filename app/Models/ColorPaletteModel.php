<?php

namespace App\Models;

use CodeIgniter\Model;

class ColorPaletteModel extends Model
{
    protected $table = 'colorpallete';  // Name of your table
    protected $primaryKey = 'id';         // Primary key of the table
    protected $allowedFields = [
        'color_name', 
        'color_code', 
        'yarn_type', 
        'color_id', 
        'yarn_material', 
        'yarn_manufacturer', 
        'status', 
        'color_tone'
    ]; // Fields that can be inserted or updated

    // Optional: You can add additional methods for custom queries or operations
}
