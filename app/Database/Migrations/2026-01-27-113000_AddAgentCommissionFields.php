<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgentCommissionFields extends Migration
{
    public function up()
    {
        // 1. Add commission_percentage to agents
        $agentFields = [
            'commission_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'agent_name'
            ],
        ];
        $this->forge->addColumn('agents', $agentFields);

        // 2. Add agent fields to invoices
        $invoiceFields = [
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'customer_id'
            ],
            'agent_commission_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'agent_id'
            ],
            'agent_commission_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'agent_commission_percent'
            ],
            'agent_commission_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Unpaid', 'Paid'],
                'default'    => 'Unpaid',
                'after'      => 'agent_commission_amount'
            ],
            'agent_commission_paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'agent_commission_status'
            ],
        ];
        $this->forge->addColumn('invoices', $invoiceFields);

        // 3. Add same fields to sales_orders
        if ($this->db->tableExists('sales_orders')) {
            $this->forge->addColumn('sales_orders', $invoiceFields);
        }

        // 4. Add same fields to quotations
        if ($this->db->tableExists('quotations')) {
            $this->forge->addColumn('quotations', $invoiceFields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('agents', 'commission_percentage');
        $this->forge->dropColumn('invoices', ['agent_id', 'agent_commission_percent', 'agent_commission_amount', 'agent_commission_status', 'agent_commission_paid_at']);
        
        if ($this->db->tableExists('sales_orders')) {
            $this->forge->dropColumn('sales_orders', ['agent_id', 'agent_commission_percent', 'agent_commission_amount', 'agent_commission_status', 'agent_commission_paid_at']);
        }
        
        if ($this->db->tableExists('quotations')) {
            $this->forge->dropColumn('quotations', ['agent_id', 'agent_commission_percent', 'agent_commission_amount', 'agent_commission_status', 'agent_commission_paid_at']);
        }
    }
}
