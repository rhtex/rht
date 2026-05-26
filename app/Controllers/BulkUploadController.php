<?php
namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\VendorModel;
use App\Models\BulkUploadLogModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkUploadController extends Controller
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    public function showForm()
    {
        return view('bulk_upload/form');
    }

    // Process uploaded file
    public function process()
    {
        $file = $this->request->getFile('file');
        $type = $this->request->getPost('type'); // 'customer' or 'vendor'
        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Invalid file upload.');
        }
        $extension = $file->getExtension();
        $tempPath = $file->getTempName();
        $rows = [];
        if (in_array(strtolower($extension), ['csv'])) {
            $handle = fopen($tempPath, 'r');
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $row = array_combine($header, $data);
                $rows[] = $row;
            }
            fclose($handle);
        } elseif (in_array(strtolower($extension), ['xlsx', 'xls'])) {
            $spreadsheet = IOFactory::load($tempPath);
            $worksheet   = $spreadsheet->getActiveSheet();
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
        } else {
            return redirect()->back()->with('error', 'Unsupported file type.');
        }

        // Basic validation: require name and phone
        $validRows = [];
        foreach ($rows as $row) {
            if (empty($row['name']) || empty($row['phone'])) {
                continue; // skip invalid
            }
            $validRows[] = $row;
        }

        $logModel = new BulkUploadLogModel();
        $logId = $logModel->insert([
            'upload_type' => $type,
            'filename'    => $file->getName(),
            'total_rows'  => count($rows),
            'inserted_rows'=> 0,
            'status'      => 'processing',
        ]);

        $inserted = 0;
        if ($type === 'customer') {
            $model = new CustomerModel();
            $inserted = $model->insertBatch($validRows);
        } elseif ($type === 'vendor') {
            $model = new VendorModel();
            $inserted = $model->insertBatch($validRows);
        }

        $logModel->update($logId, [
            'inserted_rows' => $inserted,
            'status'         => $inserted > 0 ? 'completed' : 'failed',
        ]);

        return redirect()->back()->with('message', "Upload processed. Inserted $inserted records.");
    }
}
?>
