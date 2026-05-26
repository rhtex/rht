<?php
namespace App\Models;

use CodeIgniter\Model;

class BulkUploadLogModel extends Model
{
    protected $table = 'bulk_upload_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'upload_type', 'filename', 'uploaded_by', 'uploaded_at',
        'total_rows', 'inserted_rows', 'failed_rows', 'error_report', 'status'
    ];
    protected $useTimestamps = false;
    protected $validationRules = [];
    protected $createdField = '';
    protected $updatedField = '';
}
?>
