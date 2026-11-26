<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookedToInvoiceDeliveryStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `invoices` MODIFY COLUMN `delivery_status` ENUM('Pending','Booked','In Transit','Delivered','Cancelled') NULL DEFAULT 'Pending'");
        $this->db->query("UPDATE `invoices` SET `delivery_status` = 'Booked' WHERE `delivery_status` = ''");
    }

    public function down()
    {
        $this->db->query("UPDATE `invoices` SET `delivery_status` = 'Pending' WHERE `delivery_status` = 'Booked'");
        $this->db->query("ALTER TABLE `invoices` MODIFY COLUMN `delivery_status` ENUM('Pending','In Transit','Delivered','Cancelled') NULL DEFAULT 'Pending'");
    }
}
