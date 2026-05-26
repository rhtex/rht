<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkUploadService
{
    /**
     * Parse CSV file into associative array
     */
    public function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($header, $data);
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Parse XLSX file using PhpSpreadsheet
     */
    public function parseXlsx(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = [];
        $header = [];
        foreach ($worksheet->getRowIterator() as $rowIdx => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            if ($rowIdx == 1) {
                $header = $cells;
            } else {
                $rows[] = array_combine($header, $cells);
            }
        }
        return $rows;
    }

    /**
     * Validate rows for required fields based on type.
     * Returns [validRows, errors]
     */
    public function validateRows(array $rows, string $type): array
    {
        $required = [];
        if ($type === 'customer') {
            $required = ['name', 'email', 'phone', 'address', 'city', 'state', 'zip'];
        } elseif ($type === 'vendor') {
            $required = ['name', 'contact_name', 'email', 'phone', 'address'];
        }
        $valid = [];
        $errors = [];
        foreach ($rows as $idx => $row) {
            $missing = [];
            foreach ($required as $field) {
                if (empty($row[$field] ?? null)) {
                    $missing[] = $field;
                }
            }
            if (empty($missing)) {
                $valid[] = $row;
            } else {
                $errors[$idx + 2] = 'Missing fields: ' . implode(', ', $missing);
            }
        }
        return [$valid, $errors];
    }
}
?>
