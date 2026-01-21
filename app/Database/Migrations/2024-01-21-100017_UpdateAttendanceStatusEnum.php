<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAttendanceStatusEnum extends Migration
{
    public function up()
    {
        // Update status enum to include Half Day and Holiday (replacing Leave)
        $this->db->query("ALTER TABLE `attendance` MODIFY `status` ENUM('Present', 'Absent', 'Half Day', 'Holiday') DEFAULT 'Absent'");
    }

    public function down()
    {
        // Revert back to original enum
        $this->db->query("ALTER TABLE `attendance` MODIFY `status` ENUM('Present', 'Absent', 'Leave') DEFAULT 'Absent'");
    }
}
