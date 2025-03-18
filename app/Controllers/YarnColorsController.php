<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CombinationsModel;
use App\Models\YarnColorsModel;
use App\Models\ColorPaletteModel; // Assuming you have this model

class YarnColorsController extends Controller
{
    public function insertWeftColorsFromCombinations()
    {
        // Load models
        $combinationsModel = new CombinationsModel();
        $yarnColorsModel = new YarnColorsModel();
        $colorPaletteModel = new ColorPaletteModel();

        // Step 1: Get all rows from combinations table
        $combinations = $combinationsModel->findAll();

        foreach ($combinations as $combination) {
            // Step 2: Get all weft color codes from the color_palette table
            $weftColors = $colorPaletteModel->where('yarn_type', 'weft') // Adjust condition if needed
                                             ->findAll();

            foreach ($weftColors as $weftColor) {
                // Prepare data for insertion into yarn_colors
                $weft_color_name = $weftColor['color_name'];
                $weft_color_code = $weftColor['color_code'];
                $data = [
                    'body_color_name' => $combination['body_color_name'], // Adjust field names accordingly
                    'border_color_name' => $combination['border_color_name'], // Adjust field names accordingly
                    'weft_color_name' => $weft_color_name, // Adjust field names accordingly
                    'body_color_code' => $combination['body_color'], // Adjust field names accordingly
                    'border_color_code' => $combination['border_color'], // Adjust field names accordingly
                    'weft_color_code' => $weft_color_code, // Assuming this is the field in color_palette
                    'approval' => 0 // Or whatever default you want to set
                ];

                // Step 3: Insert the data into yarn_colors table
                $yarnColorsModel->insert($data);
            }
        }

        return "Weft colors inserted successfully from combinations.";
    }
}
