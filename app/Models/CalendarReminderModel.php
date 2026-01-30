<?php

namespace App\Models;

use CodeIgniter\Model;

class CalendarReminderModel extends Model
{
    protected $table            = 'calendar_reminders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title', 'description', 'reminder_date', 'reminder_time', 'priority', 'status', 'is_notified'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get reminders due today that haven't been notified yet
     */
    public function getDueReminders()
    {
        return $this->where('reminder_date <=', date('Y-m-d'))
                    ->where('status', 'pending')
                    ->where('is_notified', 0)
                    ->findAll();
    }

    /**
     * Get all reminders for a date range (for FullCalendar)
     */
    public function getRemindersForCalendar($start, $end)
    {
        return $this->where('reminder_date >=', $start)
                    ->where('reminder_date <=', $end)
                    ->findAll();
    }
}
