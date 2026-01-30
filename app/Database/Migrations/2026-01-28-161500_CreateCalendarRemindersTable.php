<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalendarRemindersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reminder_date' => [
                'type' => 'DATE',
            ],
            'reminder_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high'],
                'default'    => 'medium',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'completed'],
                'default'    => 'pending',
            ],
            'is_notified' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('calendar_reminders');
    }

    public function down()
    {
        $this->forge->dropTable('calendar_reminders');
    }
}
