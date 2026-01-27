<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceApprovalProcess extends Migration
{
    public function up()
    {
        // 1. Modify Status Enum
        // We can't easily modify enum via Forge in a portable way with data preservation usually, 
        // but raw SQL is fine for this specific setup.
        $this->db->query("ALTER TABLE product_items MODIFY COLUMN status ENUM('received', 'available', 'sold', 'damaged', 'returned', 'rejected') DEFAULT 'received'");
        
        // 2. Modify is_approved Enum and migrate data
        $this->db->query("ALTER TABLE product_items MODIFY COLUMN is_approved ENUM('Pending', 'Approved', 'Rejected', 'Yes', 'No') DEFAULT 'Pending'");
        
        // Migrate old values
        $this->db->query("UPDATE product_items SET is_approved = 'Pending', status = 'received' WHERE is_approved = 'No'");
        $this->db->query("UPDATE product_items SET is_approved = 'Approved' WHERE is_approved = 'Yes'");
        
        // Finalize Enum (remove old values)
        $this->db->query("ALTER TABLE product_items MODIFY COLUMN is_approved ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");

        // 3. Add Rejection Columns
        $fields = [
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approved_at'
            ],
            'rejection_image' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'rejection_reason'
            ],
        ];
        $this->forge->addColumn('product_items', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('product_items', ['rejection_reason', 'rejection_image']);
        // Reverting enums is tricky with data loss, skipping strictly for now or just simplified rollback
        $this->db->query("ALTER TABLE product_items MODIFY COLUMN status ENUM('available', 'sold', 'damaged', 'returned') DEFAULT 'available'");
        $this->db->query("ALTER TABLE product_items MODIFY COLUMN is_approved ENUM('Yes', 'No') DEFAULT 'No'");
    }
}
