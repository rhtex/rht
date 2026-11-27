<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountAndAdjustmentToSalesReturns extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('discount_amount', 'sales_returns')) {
            $this->db->query("ALTER TABLE `sales_returns` ADD COLUMN `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`");
        }
        if (!$this->db->fieldExists('discount_type', 'sales_returns')) {
            $this->db->query("ALTER TABLE `sales_returns` ADD COLUMN `discount_type` VARCHAR(20) NOT NULL DEFAULT 'Fixed' AFTER `discount_amount`");
        }
        if (!$this->db->fieldExists('adjustment', 'sales_returns')) {
            $this->db->query("ALTER TABLE `sales_returns` ADD COLUMN `adjustment` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`");
        }
    }

    public function down()
    {
        foreach (['discount_amount', 'discount_type', 'adjustment'] as $col) {
            if ($this->db->fieldExists($col, 'sales_returns')) {
                $this->db->query("ALTER TABLE `sales_returns` DROP COLUMN `{$col}`");
            }
        }
    }
}
