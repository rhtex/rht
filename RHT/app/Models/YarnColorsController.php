<?php

namespace App\Controllers;

use App\Models\YarnColorsModel; // Ensure to load your model
use CodeIgniter\Controller;

class YarnColorsController extends Controller
{
    public function insertWeftColors()
    {
        // Load the database
        $db = \Config\Database::connect();
        
        // Prepare the SQL query
        $sql = "
            INSERT INTO yarn_colors (color_name, color_code, yarn_type, color_id, yarn_material, yarn_manufacturer, status, color_tone, weft_color)
            SELECT 
                c.body_color AS color_name,
                'default_code' AS color_code,  -- Replace with actual logic for color_code
                'weft' AS yarn_type,
                'default_id' AS color_id,       -- Replace with actual logic for color_id
                NULL AS yarn_material,           -- Replace with actual logic if needed
                NULL AS yarn_manufacturer,       -- Replace with actual logic if needed
                'active' AS status,
                c.color_tone AS color_tone,
                c.weft_color
            FROM 
                combinations c
            WHERE 
                c.weft_color IS NOT NULL;
        ";

        // Execute the query
        $db->query($sql);

        // Check for success
        if ($db->affectedRows() > 0) {
            return "Weft colors inserted successfully.";
        } else {
            return "No weft colors were inserted.";
        }
    }
}
