<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBulkUploadLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'upload_type' => ['type' => 'VARCHAR', 'constraint' => 20], // customer|vendor
            'filename'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'uploaded_by' => ['type' => 'INT'],
            'uploaded_at' => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'total_rows'  => ['type' => 'INT'],
            'inserted_rows'=> ['type' => 'INT'],
            'failed_rows' => ['type' => 'INT', 'null' => true],
            'error_report'=> ['type' => 'TEXT', 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20], // processing|completed|failed
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('bulk_upload_logs');
    }

    public function down()
    {
        $this->forge->dropTable('bulk_upload_logs');
    }
}
?>
