<?php

namespace App\Controllers;

use App\Models\CalendarReminderModel;
class CalendarController extends BaseController
{
    protected $reminderModel;

    public function __construct()
    {
        $this->reminderModel = new CalendarReminderModel();
    }

    /**
     * Show calendar view
     */
    public function index()
    {
        $data = [
            'title' => 'Calendar & Tasks',
            'reminders' => $this->reminderModel->orderBy('reminder_date', 'ASC')->findAll()
        ];

        return view('calendar/index', $data);
    }

    /**
     * Get reminders for FullCalendar (AJAX)
     */
    public function fetchEvents()
    {
        $start = $this->request->getGet('start');
        $end = $this->request->getGet('end');

        $reminders = $this->reminderModel->getRemindersForCalendar($start, $end);
        
        $events = [];
        foreach ($reminders as $reminder) {
            $color = '#28a745'; // Default green
            if ($reminder['priority'] === 'high') $color = '#dc3545'; // Red
            if ($reminder['priority'] === 'medium') $color = '#ffc107'; // Yellow

            $events[] = [
                'id' => $reminder['id'],
                'title' => $reminder['title'],
                'start' => $reminder['reminder_date'] . ($reminder['reminder_time'] ? 'T' . $reminder['reminder_time'] : ''),
                'description' => $reminder['description'],
                'color' => $color,
                'status' => $reminder['status']
            ];
        }

        return $this->response->setJSON($events);
    }

    /**
     * Store new reminder
     */
    public function store()
    {
        $rules = [
            'title' => 'required|min_length[3]',
            'reminder_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'reminder_date' => $this->request->getPost('reminder_date'),
            'reminder_time' => $this->request->getPost('reminder_time'),
            'priority' => $this->request->getPost('priority') ?? 'medium',
            'status' => 'pending'
        ];

        if ($this->reminderModel->save($data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Reminder added successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to save reminder']);
    }

    /**
     * Update reminder status or details
     */
    public function update($id)
    {
        $reminder = $this->reminderModel->find($id);
        if (!$reminder) {
            return $this->response->setJSON(['success' => false, 'message' => 'Reminder not found']);
        }

        $data = $this->request->getPost();
        
        if ($this->reminderModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Reminder updated successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update']);
    }

    /**
     * Delete reminder
     */
    public function delete($id)
    {
        if ($this->reminderModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Reminder deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete']);
    }

    /**
     * Check for due reminders and create notifications
     * This is designed to be called internally or via a scheduled trigger
     */
    public function checkReminders()
    {
        $dueReminders = $this->reminderModel->getDueReminders();
        
        if (empty($dueReminders)) {
            return $this->response->setJSON(['success' => true, 'count' => 0]);
        }

        $notifiedCount = 0;
        foreach ($dueReminders as $reminder) {
            $this->reminderModel->update($reminder['id'], ['is_notified' => 1]);
            $notifiedCount++;
        }

        return $this->response->setJSON(['success' => true, 'count' => $notifiedCount]);
    }
}
